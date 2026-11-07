const rating = document.querySelector('#betyg');
const summary = document.querySelector('.rating-link');
const status = rating.querySelector('output');
const choices = [...rating.querySelectorAll('input')];
const key = 'real-food:rating:' + rating.dataset.recipe;

function show(value, saved) {
  for (const choice of choices) {
    choice.checked = Number(choice.value) === value;
    choice.parentElement.classList.toggle('filled', Number(choice.value) <= value);
  }
  summary.textContent = '★ Ditt betyg: ' + value + ' av 5';
  status.textContent = 'Ditt betyg: ' + value + ' av 5.' + (saved ? ' Du kan ändra det här.' : ' Gäller i den här vyn.');
}

try {
  const previous = Number(localStorage.getItem(key));
  if (Number.isInteger(previous) && previous >= 1 && previous <= 5) show(previous, true);
} catch {}
rating.addEventListener('change', event => {
  const value = Number(event.target.value);
  let saved = false;
  try {
    localStorage.setItem(key, String(value));
    saved = true;
  } catch {}
  show(value, saved);
});
