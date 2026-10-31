"""Découpe sapocycle-hotels-map.php en 7 snippets Code Snippets.

Snippet 1 (cœur) déclare la classe Sapo_Hotels_Map à partir des traits
fournis par les snippets 2 à 7. Si un trait manque, le cœur affiche un
menu de diagnostic qui indique quel snippet n'est pas chargé.

Usage : python3 outils/split_snippets.py
"""
import os
import re

HERE = os.path.dirname(os.path.abspath(__file__))
ROOT = os.path.dirname(HERE)
SRC = os.path.join(ROOT, 'sapocycle-hotels-map.php')
OUT = os.path.join(ROOT, 'snippets')

src = open(SRC, encoding='utf-8').read()
lines = src.split('\n')


def find(title):
    """Index de la ligne « /* ==== » qui précède le titre de section."""
    for i, l in enumerate(lines):
        if l.strip() == '* ' + title:
            assert lines[i - 1].strip().startswith('/* ===')
            return i - 1
    raise SystemExit('Section introuvable : ' + title)


class_start = next(i for i, l in enumerate(lines) if l.startswith('final class Sapo_Hotels_Map'))
class_end = max(i for i, l in enumerate(lines) if l == '}')  # accolade fermante de la classe

sec = {t: find(t) for t in [
    'Bootstrap', 'Données', 'Front : shortcode', 'Administration : menu et page',
    'Administration : AJAX', 'Administration : CSS', 'Administration : JS',
]}

PARTS = [
    # (numéro, trait, fichier, libellé, début, fin)
    (2, 'Sapo_Hotels_Part_Donnees', '2-donnees', 'Données, pays, cantons, langues', sec['Données'], sec['Front : shortcode']),
    (3, 'Sapo_Hotels_Part_Carte', '3-carte-publique', 'Carte publique et shortcodes', sec['Front : shortcode'], sec['Administration : menu et page']),
    (4, 'Sapo_Hotels_Part_AdminPage', '4-admin-page', 'Page d\'administration', sec['Administration : menu et page'], sec['Administration : AJAX']),
    (5, 'Sapo_Hotels_Part_Import', '5-import-export', 'Enregistrement, import et export Excel', sec['Administration : AJAX'], sec['Administration : CSS']),
    (6, 'Sapo_Hotels_Part_AdminDesign', '6-admin-design', 'Design de l\'administration', sec['Administration : CSS'], sec['Administration : JS']),
    (7, 'Sapo_Hotels_Part_AdminScript', '7-admin-script', 'Script de l\'administration', sec['Administration : JS'], class_end),
]

HEAD = (
    '/*\n'
    ' * SapoCycle · Carte des hôtels · Snippet {n}/7 : {label}\n'
    ' * Code Snippets : type PHP, « Exécuter le snippet partout ».\n'
    ' * Les 7 snippets sont nécessaires. Le snippet 1 indique s\'il en manque un.\n'
    ' */\n'
    "if ( ! defined( 'ABSPATH' ) ) {{\n\treturn;\n}}\n"
)

os.makedirs(OUT, exist_ok=True)
for f in os.listdir(OUT):
    os.remove(os.path.join(OUT, f))

for n, trait, fname, label, a, b in PARTS:
    body = '\n'.join(lines[a:b]).rstrip() + '\n'
    code = HEAD.format(n=n, label=label)
    code += "if ( ! trait_exists( '%s', false ) ) {\n\ttrait %s {\n\n%s\t}\n}\n" % (trait, trait, body)
    open(os.path.join(OUT, '%s.txt' % fname), 'w', encoding='utf-8').write(code)

# ---- Snippet 1 : cœur ----
consts = '\n'.join(lines[class_start + 1:sec['Bootstrap']]).rstrip()
boot = '\n'.join(lines[sec['Bootstrap']:sec['Données']]).rstrip()
uses = ', '.join(p[1] for p in PARTS)
parts_php = ',\n'.join("\t\t'%s' => '%d · %s'" % (p[1], p[0], p[3].replace("'", "\\'")) for p in PARTS)

core = HEAD.format(n=1, label='Cœur et diagnostic') + '''
/** Liste des snippets attendus (trait => libellé). */
function sapo_hotels_parts() {
	return array(
%s,
	);
}

function sapo_hotels_missing_parts() {
	$missing = array();
	foreach ( sapo_hotels_parts() as $trait => $label ) {
		if ( ! trait_exists( $trait, false ) ) {
			$missing[] = $label;
		}
	}
	return $missing;
}

/** Démarre la carte une fois tous les snippets chargés (l'ordre des snippets n'a pas d'importance). */
function sapo_hotels_boot() {
	if ( class_exists( 'Sapo_Hotels_Map', false ) ) {
		return;
	}
	$missing = sapo_hotels_missing_parts();
	if ( $missing ) {
		add_action( 'admin_menu', 'sapo_hotels_diagnostic_menu' );
		add_action( 'admin_notices', 'sapo_hotels_diagnostic_notice' );
		return;
	}

	final class Sapo_Hotels_Map {
		use %s;

%s

%s
	}

	Sapo_Hotels_Map::sapo_init();
	if ( did_action( 'init' ) ) {
		Sapo_Hotels_Map::sapo_register_shortcode();
	}
}

function sapo_hotels_diagnostic_notice() {
	$missing = sapo_hotels_missing_parts();
	echo '<div class="notice notice-error"><p><strong>Carte des hôtels SapoCycle :</strong> il manque ' . count( $missing ) . ' snippet(s) sur 7 : ' . esc_html( implode( ', ', $missing ) ) . '. Ajoutez-les dans Code Snippets (type PHP, « Exécuter le snippet partout »).</p></div>';
}

function sapo_hotels_diagnostic_menu() {
	add_menu_page( 'Carte des hôtels', 'Carte des hôtels', 'edit_pages', 'sapo-hotels', 'sapo_hotels_diagnostic_page', 'dashicons-location-alt', 26 );
}

function sapo_hotels_diagnostic_page() {
	echo '<div class="wrap"><h1>Carte des hôtels : installation incomplète</h1><table class="widefat striped" style="max-width:640px"><tbody>';
	foreach ( sapo_hotels_parts() as $trait => $label ) {
		$ok = trait_exists( $trait, false );
		echo '<tr><td>' . esc_html( $label ) . '</td><td><strong style="color:' . ( $ok ? '#3c7a12' : '#b32d2e' ) . '">' . ( $ok ? 'Chargé' : 'Manquant' ) . '</strong></td></tr>';
	}
	echo '</tbody></table><p>Le snippet 1 est bien actif. Ajoutez ou réactivez les snippets marqués « Manquant », puis rechargez cette page.</p></div>';
}

if ( did_action( 'after_setup_theme' ) ) {
	sapo_hotels_boot();
} else {
	add_action( 'after_setup_theme', 'sapo_hotels_boot', 1 );
}
''' % (parts_php, uses, '\n'.join('\t' + l if l else l for l in consts.split('\n')),
       '\n'.join('\t' + l if l else l for l in boot.split('\n')))

open(os.path.join(OUT, '1-coeur.txt'), 'w', encoding='utf-8').write(core)

for f in sorted(os.listdir(OUT)):
    t = open(os.path.join(OUT, f), encoding='utf-8').read()
    print('%-24s %5d lignes %4d Ko' % (f, t.count('\n'), len(t.encode()) // 1024))
