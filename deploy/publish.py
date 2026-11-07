"""Publish project-owned components by switching their release symlinks over SFTP."""
import os
import re
import stat
import sys
import uuid
from pathlib import Path, PurePosixPath
from urllib.parse import urlsplit
from urllib.request import urlopen

SOURCE = Path("wp-content")
COMPONENTS = ("real-food-assets", "themes/real-food", "plugins/real-food")


def inventory():
    files = sorted(SOURCE.rglob("*"))
    for path in files:
        if path.is_symlink():
            raise ValueError(f"Unexpected symbolic link: {path}")
        if path.is_file() and not any(path.is_relative_to(SOURCE / part) for part in COMPONENTS):
            raise ValueError(f"Unexpected file: {path}")
    return [part for part in COMPONENTS if (SOURCE / part).is_dir()]


def mode(sftp, path):
    try:
        return sftp.lstat(str(path)).st_mode
    except FileNotFoundError:
        return None


def directory(sftp, path):
    existing = mode(sftp, path)
    if existing is None:
        sftp.mkdir(str(path))
    elif not stat.S_ISDIR(existing):
        raise ValueError(f"Expected directory: {path}")


def switch(sftp, path, target):
    temporary = str(path) + "." + uuid.uuid4().hex
    sftp.symlink(target, temporary)
    try:
        sftp.posix_rename(temporary, str(path))
    except Exception:
        sftp.remove(temporary)
        raise


def verify(url, expected):
    with urlopen(url, timeout=25) as response:
        if response.geturl() != url or response.read(len(expected) + 1) != expected:
            raise RuntimeError("The release could not be verified at the configured URL")


def publish(sftp, parts):
    root = PurePosixPath(os.environ["WP_ROOT"])
    base = os.environ["WP_URL"].rstrip("/")
    url = urlsplit(base)
    production = url.hostname in {"axenhorn.se", "www.axenhorn.se"} and url.path in {"", "/"}
    if url.scheme != "https" or not url.hostname or url.query or url.fragment or url.username:
        raise ValueError("WP_URL must be an HTTPS WordPress installation URL")
    if production != (os.environ["TARGET"] == "production"):
        raise ValueError("WordPress URL does not match the environment")
    if not root.is_absolute() or ".." in root.parts:
        raise ValueError("WP_ROOT must be an absolute SFTP path")
    sftp.stat(str(root / "wp-includes/version.php"))
    content = root / "wp-content"
    store = content / "real-food-releases"
    directory(sftp, store)
    revision = os.environ["GITHUB_SHA"]
    if not re.fullmatch(r"[0-9a-f]{40}", revision):
        raise ValueError("Invalid Git revision")
    release = store / (revision + "-" + uuid.uuid4().hex)
    directory(sftp, release)
    stamp = release.name.encode()
    previous = {}
    for part in parts:
        live = content / part
        existing = mode(sftp, live)
        if existing is not None:
            if not stat.S_ISLNK(existing):
                raise ValueError(f"Existing unmanaged path: {live}")
            original = sftp.readlink(str(live))
            if not PurePosixPath(original).is_relative_to(store) or ".." in PurePosixPath(original).parts:
                raise ValueError(f"Unmanaged symbolic link: {live}")
            previous[part] = original
        # Themes and plugins can have the same WordPress slug.
        destination = release / part.replace("/", "--")
        directory(sftp, destination)
        for path in sorted((SOURCE / part).rglob("*")):
            remote = destination / path.relative_to(SOURCE / part)
            if path.is_dir():
                directory(sftp, remote)
            else:
                sftp.put(str(path), str(remote))
        with sftp.open(str(destination / "real-food-release.txt"), "wb") as marker:
            marker.write(stamp)
    marker_name = "real-food-release.txt"
    first = parts[0].replace("/", "--")
    verify(f"{base}/wp-content/real-food-releases/{release.name}/{first}/{marker_name}", stamp)
    changed = []
    try:
        for part in parts:
            switch(sftp, content / part, str(release / part.replace("/", "--")))
            changed.append(part)
        for part in parts:
            verify(f"{base}/wp-content/{part}/{marker_name}?v={release.name}", stamp)
        with urlopen(base + "/", timeout=25) as response:
            if response.status != 200 or not response.read(1):
                raise RuntimeError("WordPress home page failed its health check")
    except Exception:
        for part in reversed(changed):
            if part in previous:
                switch(sftp, content / part, previous[part])
            else:
                sftp.remove(str(content / part))
        raise
    print(f"Verified {revision} on {os.environ['TARGET']}")


def main():
    parts = inventory()
    if not parts:
        raise ValueError("No deployable components")
    if sys.argv[1:] == ["--check"]:
        print("Deployable components:", ", ".join(parts))
        return
    import paramiko
    class OneComHostKey(paramiko.MissingHostKeyPolicy):
        def missing_host_key(self, client, hostname, key):
            if key.get_name() != "ssh-ed25519" or key.fingerprint != "SHA256:iDgAh/1dj9HyXk3nOztS6nK7jJI1IyR2uUrxDCF9kI8":
                raise paramiko.SSHException("Unexpected One.com host key")

    with paramiko.SSHClient() as client:
        client.set_missing_host_key_policy(OneComHostKey())
        client.connect(os.environ["SFTP_HOST"], username=os.environ["SFTP_USER"],
                       password=os.environ["SFTP_PASSWORD"], look_for_keys=False,
                       allow_agent=False, timeout=20, auth_timeout=20)
        with client.open_sftp() as sftp:
            publish(sftp, parts)


if __name__ == "__main__":
    main()
