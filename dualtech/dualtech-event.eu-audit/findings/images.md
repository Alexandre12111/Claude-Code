# 6. Images (65 / 100)

* Attributs `alt` : présents sur toutes les images (les images décoratives ont un `alt` vide, ce qui est correct).
* Dimensions `width` et `height` : présentes partout sauf le logo PNG du pied de page Elementor (`Screenshot_2026-04-03_153343-removebg-preview.png`, nom de fichier non descriptif, 48 Ko de trop).
* Formats : WebP pour toutes les images du thème.
* Problème principal : photos de filières surdimensionnées (voir Performance).
* L'attribut `loading="lazy"` est écrit deux fois sur certaines images (ajouté par WordPress en plus de celui du thème) : sans effet visible, mais HTML invalide.
