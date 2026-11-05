"""Ressources des applications de la présentation : icônes Lucide utilisées et portraits (Unsplash, voir CREDITS) réduits en 96 px."""
import base64, io, json, os, re, subprocess
HERE = os.path.dirname(os.path.abspath(__file__))
VID = os.path.join(HERE, '..', 'video', 'digicraft-lancement')
I = json.loads(re.search(r'window\.ICONS=(\{.*\})', open(os.path.join(VID, 'src6', 'icons.js'), encoding='utf-8').read(), re.S).group(1))
EXTRA = {
    'truck': '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
    'thermometer': '<path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>',
    'wrench': '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>',
    'file-text': '<path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>',
    'cpu': '<rect width="16" height="16" x="4" y="4" rx="2"/><rect width="6" height="6" x="9" y="9" rx="1"/><path d="M15 2v2"/><path d="M15 20v2"/><path d="M2 15h2"/><path d="M2 9h2"/><path d="M20 15h2"/><path d="M20 9h2"/><path d="M9 2v2"/><path d="M9 20v2"/>',
    'arrow-up': '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
    'paperclip': '<path d="m21.44 11.05-9.19 9.19a6 6 0 0 1-8.49-8.49l8.57-8.57A4 4 0 1 1 18 8.84l-8.59 8.57a2 2 0 0 1-2.83-2.83l8.49-8.48"/>',
}
JS = open(os.path.join(HERE, 'apps.js'), encoding='utf-8').read() + open(os.path.join(HERE, 'apps2.js'), encoding='utf-8').read() if os.path.exists(os.path.join(HERE, 'apps2.js')) else open(os.path.join(HERE, 'apps.js'), encoding='utf-8').read()
used = set(re.findall(r"ic\('([a-z0-9-]+)'", JS)) | set(re.findall(r"\['([a-z0-9-]+)', '", JS)) | {'house', 'bell', 'settings', 'users', 'map-pin', 'gauge', 'building-2', 'truck', 'sparkles', 'monitor', 'smartphone', 'code-xml', 'rocket', 'arrow-up', 'paperclip', 'check', 'x', 'external-link', 'send', 'list-checks', 'layout-dashboard', 'chart-column', 'wand-sparkles', 'message-square-text', 'bot' }
icons = dict(I); icons.update(EXTRA)
photos = {}
src = os.path.join(VID, 'assets', 'img', 'people')
for f in sorted(os.listdir(src)):
    if not f.endswith('.jpg'): continue
    out = subprocess.run(['ffmpeg', '-v', 'error', '-i', os.path.join(src, f), '-vf', 'scale=96:96', '-q:v', '5', '-f', 'mjpeg', '-'], capture_output=True, check=True).stdout
    photos[f[:-4]] = 'data:image/jpeg;base64,' + base64.b64encode(out).decode()
js = 'window.YA_ICONS=' + json.dumps(icons) + ';\nwindow.YA_PHOTOS=' + json.dumps(photos) + ';\n'
open(os.path.join(HERE, 'apps_assets.js'), 'w').write(js)
print(len(icons), 'icônes', len(photos), 'photos', round(len(js) / 1024), 'Ko', sorted(set(used) - set(icons)))
