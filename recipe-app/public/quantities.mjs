const fractions={'½':.5,'¼':.25,'¾':.75,'⅓':1/3,'⅔':2/3,'⅛':.125,'⅜':.375,'⅝':.625,'⅞':.875};
const atom='(?:\\d+\\s+\\d+\\/\\d+|\\d+\\/\\d+|\\d+[½¼¾⅓⅔⅛⅜⅝⅞]|\\d+(?:[.,]\\d+)?|[½¼¾⅓⅔⅛⅜⅝⅞])';
function number(s){s=s.trim().replace(',','.');const ch=s.at(-1);if(ch in fractions)return (Number(s.slice(0,-1))||0)+fractions[ch];if(s.includes('/')){const a=s.split(/\s+/);const [n,d]=a.pop().split('/').map(Number);return d? (Number(a[0])||0)+n/d:NaN;}return Number(s);}
const fmt=n=>new Intl.NumberFormat('sv-SE',{maximumFractionDigits:2}).format(n);
export function scale(line,factor){
 if(!Number.isFinite(factor)||factor<=0||factor===1)return line;
 const m=line.match(new RegExp('^(\\s*(?:ca\\.?\\s+)?)(('+atom+')(?:\\s*[-–]\\s*('+atom+'))?)(.*)$','u'));
 if(!m)return line;
 const a=number(m[3]),b=m[4]?number(m[4]):null;
 // Oklara uttryck och "2 x 400 g" behålls för manuell bedömning.
 if(!Number.isFinite(a)||(b!==null&&!Number.isFinite(b))||/^\s*(?:x|×|eller\b|or\b|till\b)/iu.test(m[5]))return line;
 return m[1]+fmt(a*factor)+(b!==null?'–'+fmt(b*factor):'')+m[5];
}
