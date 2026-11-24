"""Mise à jour de la v1.9 (fichier modifié sur place) :
1) 1300+ utilisateurs chez Leyton (chiffre animé, phrase et jauge sur 3 000 collaborateurs) ;
2) retrait des 7 jours offerts (bandeau d'offre, prix barré « Offert » de Business, mention sous le prix,
   encart « 7 jours pour essayer… accès gratuit » de la diapositive Prochaines étapes) ;
3) prix exprimés par créateur (25 / 50 / 100 € HT / créateur / mois), les licences utilisateur restent inchangées."""
import os, re

F = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'youcraft-presentation_1_9.html')
src = open(F, encoding='utf-8').read()
B64 = []
def stash(m):
    B64.append(m.group(0)); return f'@@B64_{len(B64) - 1}@@'
src = re.sub(r'data:[a-z/+.-]+;base64,[A-Za-z0-9+/=]+', stash, src)


def rep(a, b, n=1):
    global src
    c = src.count(a)
    assert c == n, (c, a[:90])
    src = src.replace(a, b)


# 1. 1300+ utilisateurs
rep('data-count="1000" data-suffix="+">1000+</div><span>Users at Leyton</span>', 'data-count="1300" data-suffix="+">1300+</div><span>Users at Leyton</span>')
rep('<p><b>1000+</b> of the group’s <b>3,000</b>', '<p><b>1300+</b> of the group’s <b>3,000</b>')
rep('.kf-ad .kf-bar i{width:33.3%}', '.kf-ad .kf-bar i{width:43.3%}')

# 2. Plus d'offre de 7 jours gratuits
rep('    <div class="pz-offer rise" style="--d:.1s"><b>Leyton client launch offer:</b> <span>7 days of Business free, renewable.</span></div>\n', '')
rep('<div class="pc-v"><s>50</s><b>Free</b></div>', '<div class="pc-v"><b>50</b></div>')
rep('<div class="pc-o">7 days free, renewable · Leyton clients</div>', '')
rep('    <div class="ns-ft rise" style="--d:.85s"><span class="ns-fi"><svg class="ic"><use href="#i-spark"/></svg></span><b>7 days to try it, renewable, no commitment</b><span class="ns-fs">Free access during the trial</span></div>\n', '')

# 3. Prix par créateur (les licences utilisateur d'application ne changent pas)
rep('<span>€ excl. VAT / user / month</span>', '<span>€ excl. VAT / creator / month</span>', 3)
for a, b in [('"€ excl. VAT / user / month": "€ HT / utilisateur / mois"', '"€ excl. VAT / creator / month": "€ HT / créateur / mois"'),
             ('"€ excl. VAT / user / month": "€ sin IVA / usuario / mes"', '"€ excl. VAT / creator / month": "€ sin IVA / creador / mes"'),
             ('"€ excl. VAT / user / month": "€ exkl. moms / användare / månad"', '"€ excl. VAT / creator / month": "€ exkl. moms / skapare / månad"'),
             ('"€ excl. VAT / user / month": "€ zzgl. MwSt. / Nutzer / Monat"', '"€ excl. VAT / creator / month": "€ zzgl. MwSt. / Creator / Monat"'),
             ('"€ excl. VAT / user / month": "€ excl. VAT / user / month"', '"€ excl. VAT / creator / month": "€ excl. VAT / creator / month"')]:
    rep(a, b)

src = re.sub(r'@@B64_(\d+)@@', lambda m: B64[int(m.group(1))], src)
open(F, 'w', encoding='utf-8').write(src)
print('ok', round(len(src) / 1024), 'Ko')
