#!/bin/bash
# Version Irlande (IE_EN) à la charte YouCraft : image src_yb_ie (euros, région « Munster », « Hosted in Ireland »).
# Voix off : les répliques de la version EN validée, sauf la phrase sécurité régénérée avec « Ireland »
# (sherpa-onnx 1.13.8, qui reproduit la voix EN ; la 2.0 saute des mots absents du lexique).
# Prérequis : bash render/render_yb_queue.sh ie, puis stems music_en / sfx_en (DC_VARIANT=en) et python3 audio/mix_markets.py ie
cd "$(dirname "$0")/.."
export YC_BRAND=1
(cd audio && python3 subs_markets.py ie) && python3 render/burn_markets.py ie
