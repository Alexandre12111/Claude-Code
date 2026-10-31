import re,base64,subprocess,sys
src=sys.argv[1]; out=sys.argv[2]
s=open(src).read()
# 1) CSS et JS stockés en base64 (aucun motif HTML/JS visible pour les pare-feu)
def enc(m):
    body=m.group(2)
    return "return base64_decode( '"+base64.b64encode(body.encode()).decode()+"' );"
s,n=re.subn(r"return <<<'(CSS|JS)'\n(.*?)\n\1;",enc,s,flags=re.S)
assert n==4,n
tmp=out+'.tmp.php'; open(tmp,'w').write(s)
# 2) Minification sûre (php -w : retire commentaires et espaces, garde les chaînes)
m=subprocess.run(['php','-w',tmp],capture_output=True,text=True,check=True).stdout
import os; os.remove(tmp)
assert m.startswith('<?php')
m=m[len('<?php'):].lstrip()
open(out,'w').write("/* SapoCycle : carte des hôtels (snippet PHP, exécuter partout) */\n"+m.rstrip()+"\n")
