/* Aurea Compta : interface locale (aucune dépendance externe). */
"use strict";

// ------------------------------------------------------------------ utilitaires
const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const esc = (v) => String(v ?? "").replace(/[&<>"']/g, (c) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c]));
const eur = (c) => (c === null || c === undefined || c === "") ? "–" : new Intl.NumberFormat("fr-FR", { style: "currency", currency: "EUR" }).format(c / 100);
const eurSigned = (c) => `<span class="num ${c < 0 ? "neg" : c > 0 ? "pos" : ""}">${eur(c)}</span>`;
const d = (s) => s ? new Date(s.length === 10 ? s + "T12:00:00" : s).toLocaleDateString("fr-FR") : "";
const today = () => new Date().toISOString().slice(0, 10);
const thisMonth = () => new Date().toISOString().slice(0, 7);
const toCents = (v) => { if (v === "" || v === null || v === undefined) return null; const n = String(v).replace(/\s/g, "").replace(",", "."); if (isNaN(Number(n))) throw new Error("Montant invalide : " + v); return Math.round(Number(n) * 100); };
const centsToInput = (c) => (c === null || c === undefined) ? "" : (c / 100).toFixed(2).replace(".", ",");

const ICONS = {"home": "<path d=\"M3 10.5 12 3l9 7.5\"/><path d=\"M5 9.5V21h14V9.5\"/><path d=\"M10 21v-6h4v6\"/>", "bank": "<path d=\"M3 21h18\"/><path d=\"M5 21v-9M9.5 21v-9M14.5 21v-9M19 21v-9\"/><path d=\"M2 9.5 12 3l10 6.5z\"/>", "invoice": "<path d=\"M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z\"/><path d=\"M14 3v6h6M8 13h8M8 17h5\"/>", "card": "<rect x=\"2.5\" y=\"5\" width=\"19\" height=\"14\" rx=\"2\"/><path d=\"M2.5 10h19M6 15h4\"/>", "user": "<circle cx=\"12\" cy=\"8\" r=\"4\"/><path d=\"M4 21a8 8 0 0 1 16 0\"/>", "percent": "<path d=\"M19 5 5 19\"/><circle cx=\"6.5\" cy=\"6.5\" r=\"2.5\"/><circle cx=\"17.5\" cy=\"17.5\" r=\"2.5\"/>", "book": "<path d=\"M2 4h6a4 4 0 0 1 4 4v13a3 3 0 0 0-3-3H2z\"/><path d=\"M22 4h-6a4 4 0 0 0-4 4v13a3 3 0 0 1 3-3h7z\"/>", "archive": "<rect x=\"2\" y=\"3\" width=\"20\" height=\"5\" rx=\"1\"/><path d=\"M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8M10 12h4\"/>", "sliders": "<path d=\"M4 21v-7M4 10V3M12 21v-9M12 8V3M20 21v-5M20 12V3M1 14h6M9 8h6M17 16h6\"/>", "search": "<circle cx=\"11\" cy=\"11\" r=\"7\"/><path d=\"m20 20-3.5-3.5\"/>", "sparkles": "<path d=\"M12 3l1.8 4.7 4.7 1.8-4.7 1.8L12 16l-1.8-4.7-4.7-1.8 4.7-1.8z\"/><path d=\"M19 15l.8 2.2 2.2.8-2.2.8L19 21l-.8-2.2-2.2-.8 2.2-.8z\"/>", "moon": "<path d=\"M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z\"/>", "sun": "<circle cx=\"12\" cy=\"12\" r=\"4\"/><path d=\"M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4\"/>", "menu": "<path d=\"M4 6h16M4 12h16M4 18h16\"/>", "x": "<path d=\"M18 6 6 18M6 6l12 12\"/>", "external": "<path d=\"M15 3h6v6M10 14 21 3M21 14v5a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5\"/>", "upload": "<path d=\"M12 16V4M7 9l5-5 5 5\"/><path d=\"M4 16v3a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-3\"/>", "download": "<path d=\"M12 4v12M7 11l5 5 5-5\"/><path d=\"M4 20h16\"/>", "sync": "<path d=\"M21 12a9 9 0 0 1-15.5 6.2L3 16M3 12a9 9 0 0 1 15.5-6.2L21 8\"/><path d=\"M21 3v5h-5M3 21v-5h5\"/>", "check": "<circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"m8 12 3 3 5-6\"/>", "alert": "<path d=\"M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z\"/><path d=\"M12 9v4M12 17h.01\"/>", "info": "<circle cx=\"12\" cy=\"12\" r=\"9\"/><path d=\"M12 16v-4M12 8h.01\"/>", "wallet": "<path d=\"M20 7H5a2 2 0 0 1 0-4h13v4\"/><path d=\"M3 5v14a2 2 0 0 0 2 2h15V7\"/><circle cx=\"16\" cy=\"14\" r=\"1.2\"/>", "up": "<path d=\"m3 17 6-6 4 4 8-8\"/><path d=\"M14 7h7v7\"/>", "down": "<path d=\"m3 7 6 6 4-4 8 8\"/><path d=\"M14 17h7v-7\"/>", "scale": "<path d=\"M12 3v18M7 21h10M5 7h14\"/><path d=\"M5 7 2 14a3 3 0 0 0 6 0zM19 7l-3 7a3 3 0 0 0 6 0z\"/>", "inbox": "<path d=\"M22 12h-6l-2 3h-4l-2-3H2\"/><path d=\"M5.5 5h13L22 12v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z\"/>", "more": "<circle cx=\"5\" cy=\"12\" r=\"1.2\"/><circle cx=\"12\" cy=\"12\" r=\"1.2\"/><circle cx=\"19\" cy=\"12\" r=\"1.2\"/>", "plus": "<path d=\"M12 5v14M5 12h14\"/>", "calendar": "<rect x=\"3\" y=\"4.5\" width=\"18\" height=\"16\" rx=\"2\"/><path d=\"M3 9.5h18M8 3v3M16 3v3\"/>", "route":"<circle cx=\"6\" cy=\"19\" r=\"3\"/><circle cx=\"18\" cy=\"5\" r=\"3\"/><path d=\"M9 19h8.5a3.5 3.5 0 0 0 0-7h-11a3.5 3.5 0 0 1 0-7H15\"/>", "scooter":"<circle cx=\"5.5\" cy=\"17.5\" r=\"2.5\"/><circle cx=\"18.5\" cy=\"17.5\" r=\"2.5\"/><path d=\"M8 17.5h8l2-6h-4l-2 6M15 5h2l2 6.5M3 13h6\"/>", "file": "<path d=\"M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z\"/><path d=\"M14 3v6h6\"/>"};
// Sprite SVG unique (icônes au trait, style Lucide) : utilisé par le gabarit et par les vues.
document.body.insertAdjacentHTML("afterbegin", `<svg width="0" height="0" style="position:absolute" aria-hidden="true">${Object.entries(ICONS).map(([k, v]) => `<symbol id="i-${k}" viewBox="0 0 24 24">${v}</symbol>`).join("")}</svg>`);
const ic = (name, label) => `<svg class="icon" ${label ? `role="img" aria-label="${label}"` : 'aria-hidden="true"'}><use href="#i-${name}"/></svg>`;
const emptyState = (text, icon = "inbox") => `<div class="empty">${ic(icon)}<div>${text}</div></div>`;

// Les chemins « /api/xxx » de l'interface correspondent à l'API REST WordPress « aurea/v1/xxx ».
function restUrl(path, withNonce = false) {
  const [p, qs] = path.replace(/^\/api\//, "").split("?");
  let u = AC.root + p;
  const extra = [qs, withNonce ? "_wpnonce=" + AC.nonce : ""].filter(Boolean).join("&");
  if (extra) u += (u.includes("?") ? "&" : "?") + extra;
  return u;
}
// Liens de téléchargement (PDF, ZIP, justificatifs) : authentifiés par le jeton WordPress.
document.addEventListener("click", (e) => {
  const a = e.target.closest && e.target.closest("a[href^='/api/']");
  if (a) a.href = restUrl(a.getAttribute("href"), true);
}, true);

async function api(path, opts = {}) {
  path = restUrl(path);
  const o = { credentials: "same-origin", headers: { "X-WP-Nonce": AC.nonce }, ...opts };
  if (o.body && !(o.body instanceof FormData)) { o.headers["Content-Type"] = "application/json"; o.body = JSON.stringify(o.body); }
  const r = await fetch(path, o);
  if (!r.ok) {
    let msg = r.statusText;
    try { const j = await r.json(); msg = typeof j.detail === "string" ? j.detail : (j.message || JSON.stringify(j.detail)); } catch (e) { /* ignore */ }
    if (r.status === 401 || r.status === 403) msg = "Session WordPress expirée : rechargez la page (" + msg + ").";
    throw new Error(msg);
  }
  const ct = r.headers.get("content-type") || "";
  return ct.includes("json") ? r.json() : r;
}
const post = (p, body) => api(p, { method: "POST", body: body || {} });
const put = (p, body) => api(p, { method: "PUT", body });
const del = (p) => api(p, { method: "DELETE" });

function toast(msg, err = false) {
  const t = document.createElement("div");
  t.className = "toast" + (err ? " err" : "");
  t.setAttribute("role", err ? "alert" : "status");
  t.innerHTML = ic(err ? "alert" : "check") + `<span>${esc(msg)}</span>`;
  $("#toasts").appendChild(t);
  setTimeout(() => t.remove(), err ? 7000 : 3500);
}
async function run(fn, okMsg) {
  try { const r = await fn(); if (okMsg) toast(okMsg); return r; } catch (e) { toast(e.message, true); throw e; }
}
let lastFocus = null;
function openDrawer(html) {
  if (!$("#drawer").classList.contains("open")) lastFocus = document.activeElement;
  $("#drawer-body").innerHTML = html;
  const h = $("#drawer-body h2"); if (h) h.id = "drawer-title";
  $("#drawer").classList.add("open"); $("#drawer").setAttribute("aria-hidden", "false");
  $("#drawer .drawer-panel").scrollTop = 0;
  setTimeout(() => $("#drawer .close").focus({ preventScroll: true }), 30);
}
function closeDrawer() {
  if (!$("#drawer").classList.contains("open")) return;
  $("#drawer").classList.remove("open"); $("#drawer").setAttribute("aria-hidden", "true");
  if (lastFocus && document.contains(lastFocus)) lastFocus.focus({ preventScroll: true });
}
function toggleSidebar(force) {
  const sb = $("#sidebar"); const open = force === undefined ? !sb.classList.contains("open") : force;
  sb.classList.toggle("open", open);
  $(".menu").setAttribute("aria-expanded", String(open));
  let scrim = $(".scrim");
  if (open && !scrim) { scrim = document.createElement("div"); scrim.className = "scrim"; scrim.onclick = () => toggleSidebar(false); document.body.appendChild(scrim); }
  if (!open && scrim) scrim.remove();
}
window.addEventListener("scroll", () => $("#topbar") && $("#topbar").classList.toggle("scrolled", window.scrollY > 4), { passive: true });
$("#drawer").addEventListener("click", (e) => { if (e.target.id === "drawer") closeDrawer(); });
function openModal(html) { $("#modal-body").innerHTML = html; $("#modal").classList.add("open"); setTimeout(() => { const f = $("#modal-body input, #modal-body select, #modal-body button"); if (f) f.focus(); }, 30); }
function closeModal() { $("#modal").classList.remove("open"); }
$("#modal").addEventListener("click", (e) => { if (e.target.id === "modal") closeModal(); });
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") { closeDrawer(); closeModal(); toggleSidebar(false); }
  if (e.key === "Enter" && e.target.matches && e.target.matches("tr.click, .card.link")) e.target.click();
  if (e.key === "/" && !["INPUT", "TEXTAREA", "SELECT"].includes(document.activeElement.tagName)) { e.preventDefault(); openSearch(); }
});
function formData(form) {
  const o = {};
  $$("[name]", form).forEach((el) => { o[el.name] = el.type === "checkbox" ? el.checked : el.value; });
  return o;
}
function setActions(html) { $("#top-actions").innerHTML = html || ""; }
function toggleTheme() {
  const cur = document.documentElement.dataset.theme || (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light");
  const next = cur === "dark" ? "light" : "dark";
  document.documentElement.dataset.theme = next;
  try { localStorage.setItem("theme", next); } catch (e) { /* stockage indisponible */ }
  themeIcon();
}
function themeIcon() {
  const dark = (document.documentElement.dataset.theme || (matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light")) === "dark";
  const b = $("#theme-btn"); if (b) b.innerHTML = ic(dark ? "sun" : "moon");
}
try { const t = localStorage.getItem("theme"); if (t) document.documentElement.dataset.theme = t; } catch (e) { /* */ }

const STATUS = {
  a_traiter: ["À catégoriser", "b-warn"], propose: ["Proposition", "b-info"], rapproche: ["Rapprochée", "b-ok"], ignore: ["Ignorée", ""],
  importe: ["Importée", "b-info"], propose_ia: ["Proposée par l'IA", "b-ai"], a_valider: ["À valider", "b-warn"], valide: ["Validée", "b-ok"],
  brouillon: ["Provisoire", "b-warn"], emise: ["Émise", ""], annulee: ["Annulée", "b-bad"],
  payee: ["Payée", "b-ok"], partielle: ["Partiellement payée", "b-warn"], impayee: ["Impayée", "b-bad"], rembourse: ["Remboursé", "b-ok"], impute: ["Imputé", "b-info"],
  preparee: ["Préparée", "b-info"], validee: ["Validée", "b-ok"], deposee: ["Déposée", "b-ok"],
  bloquant: ["Bloquant", "b-bad"], a_verifier: ["À vérifier", "b-warn"], info: ["Info", "b-info"],
};
const badge = (s) => { const [l, c] = STATUS[s] || [s, ""]; return `<span class="badge ${c}">${esc(l)}</span>`; };
const PAYMENT = { qonto: "Compte Qonto", perso: "Payée personnellement", a_payer: "À payer" };
const REGIMES = { fr: "TVA française", sans_tva: "Sans TVA", autoliquidation_ue: "Autoliquidation (fournisseur UE)", autoliquidation_hors_ue: "Autoliquidation (hors UE)" };

let CACHE = { categories: null, accounts: null };
async function categories() { return CACHE.categories ||= await api("/api/categories"); }
async function accountsList() { return CACHE.accounts ||= await api("/api/accounts"); }

function entriesHtml(entries) {
  if (!entries || !entries.length) return `<p class="muted small">Aucune écriture.</p>`;
  return entries.filter(Boolean).map((e) => `
    <div class="entry"><div class="entry-head"><span><b>${esc(e.journal)}</b> · ${d(e.date)} · ${esc(e.label)}</span>
      <span>${e.number ? `<span class="mono">${esc(e.number)}</span>` : ""} ${badge(e.status)}</span></div>
      <table>${e.lines.map((l) => `<tr><td class="mono">${esc(l.account)}</td><td>${esc(l.account_label || "")}${l.aux ? ` <span class="muted">· ${esc(l.aux)}</span>` : ""}</td>
        <td class="num">${l.debit_cents ? eur(l.debit_cents) : ""}</td><td class="num">${l.credit_cents ? eur(l.credit_cents) : ""}</td></tr>`).join("")}</table></div>`).join("");
}
function issuesHtml(issues, empty = "Aucun problème à corriger.") {
  if (!issues.length) return `<div class="alert ok">${empty}</div>`;
  return `<div class="panel"><ul class="issues">${issues.map((i) => `<li>${badge(i.niveau)}<span style="flex:1">${esc(i.message)}</span>${i.lien ? `<a href="${i.lien}" onclick="closeDrawer();closeModal()">Corriger</a>` : ""}</li>`).join("")}</ul></div>`;
}
function docLink(doc) { return doc ? `<a class="doc-link" href="/api/documents/${doc.id}/file" target="_blank">${ic("file")}${esc(doc.original_name || "Justificatif")}</a>` : `<span class="badge b-warn">Justificatif manquant</span>`; }

// ------------------------------------------------------------------ routeur
const ROUTES = {};
const TITLES = { "": "Vue d'ensemble", operations: "Opérations", banque: "Opérations", recettes: "Recettes et factures clients", depenses: "Opérations",
  associe: "Apports personnels et compte courant", tva: "TVA", comptabilite: "Comptabilité et écritures", cloture: "Clôture annuelle et exports", parametres: "Paramètres" };
function params() { const q = location.hash.split("?")[1] || ""; return Object.fromEntries(new URLSearchParams(q)); }
async function route(keepOverlay = false) {
  if (keepOverlay !== true) { closeDrawer(); closeModal(); }
  const name = (location.hash.replace(/^#\/?/, "").split("?")[0]) || "";
  const navName = ["banque", "depenses"].includes(name) ? "operations" : name;
  $$("#nav a, .bottom-nav a").forEach((a) => { if (a.dataset.route === navName) a.setAttribute("aria-current", "page"); else a.removeAttribute("aria-current"); });
  $("#page-title").textContent = TITLES[name] || "";
  toggleSidebar(false);
  setActions("");
  $("#view").innerHTML = `<div class="skeleton" aria-busy="true" aria-label="Chargement"><div></div><div></div><div></div></div>`;
  try { await (ROUTES[name] || ROUTES[""])(params()); } catch (e) { $("#view").innerHTML = `<div class="alert bad">${esc(e.message)}</div>`; }
  refreshCounts();
}
window.addEventListener("hashchange", () => route());
async function refreshCounts() {
  try {
    const o = await api("/api/overview");
    $("#c-operations").textContent = o.a_verifier.operations || "";
    $("#b-operations").textContent = $("#c-operations").textContent;
    const prov = o.parametres_provisoires;
    $("#banner").innerHTML = (o.mode_demo ? `<div class="alert info">Mode démonstration : données fictives séparées de votre base réelle.</div>` : "") +
      (prov.length ? `<div class="alert warn">${ic("alert")}<span>Paramètres provisoires utilisés (${prov.map((p) => p === "tax" ? "régimes fiscaux" : "société").join(", ")}). <a href="#/parametres">Les confirmer</a></span></div>` : "");
  } catch (e) { /* ignore */ }
}

// ------------------------------------------------------------------ vue d'ensemble
const kpi = (href, icon, label, value, sub, cls = "") => `<div class="card link ${cls}" role="link" tabindex="0" onclick="location.hash='${href}'"><div class="label">${ic(icon)}${label}</div><div class="value">${value}</div><div class="sub">${sub}</div></div>`;
ROUTES[""] = async () => {
  const o = await api("/api/overview");
  setActions(`<button onclick="syncQonto()">${ic("sync")}Synchroniser Qonto</button><button class="primary" onclick="location.hash='#/comptabilite?tab=mois'">${ic("check")}Vérifier le mois</button>`);
  const tva = o.tva || {};
  const tvaVal = tva.credit_cents ? eur(tva.credit_cents) : eur(tva.a_payer_cents || 0);
  const todo = o.a_verifier;
  const totalTodo = todo.operations + todo.depenses + todo.justificatifs_manquants + todo.pieces_non_traitees;
  $("#view").innerHTML = `
    <div class="cards">
      ${kpi("#/operations?tab=toutes", "wallet", "Solde bancaire", eur(o.solde_bancaire_cents), esc(o.solde_source), "hero")}
      ${kpi("#/recettes", "up", "Recettes de l'exercice (HT)", eur(o.recettes_cents), `Facturé du ${d(o.exercice.start_date)} au ${d(o.exercice.end_date)}`)}
      ${kpi("#/operations?tab=depenses", "down", "Dépenses de l'exercice (HT)", eur(o.depenses_cents), "Charges comptables, amortissements compris")}
      ${kpi("#/cloture", "scale", "Résultat provisoire", `<span class="${o.resultat_cents < 0 ? "neg" : ""}">${eur(o.resultat_cents)}</span>`, (o.resultat_cents >= 0 ? "Bénéficiaire" : "Déficitaire") + " avant impôt")}
      ${kpi("#/tva", "percent", tva.credit_cents ? "Crédit de TVA estimé" : "TVA estimée à payer", tvaVal, "Période " + esc(tva.periode || ""))}
      ${kpi("#/associe", "user", "Apports non remboursés", eur(o.apports_non_rembourses_cents), "Somme due par la société")}
      ${kpi("#/operations", "inbox", "Opérations à vérifier", String(totalTodo), `${todo.operations} opération(s), ${todo.depenses} dépense(s), ${todo.justificatifs_manquants} justificatif(s) manquant(s)`)}
    </div>
    <h2>À faire</h2>
    <div class="panel"><ul class="issues">
      ${todo.operations ? `<li>${badge("a_verifier")}<span style="flex:1">${todo.operations} opération(s) bancaire(s) à catégoriser</span><a href="#/operations">Ouvrir</a></li>` : ""}
      ${todo.pieces_non_traitees ? `<li>${badge("a_verifier")}<span style="flex:1">${todo.pieces_non_traitees} justificatif(s) déposé(s) non rattaché(s)</span><a href="#/operations?tab=pieces">Ouvrir</a></li>` : ""}
      ${todo.depenses ? `<li>${badge("a_verifier")}<span style="flex:1">${todo.depenses} dépense(s) à valider</span><a href="#/operations?tab=depenses&status=a_valider">Ouvrir</a></li>` : ""}
      ${todo.justificatifs_manquants ? `<li>${badge("info")}<span style="flex:1">${todo.justificatifs_manquants} dépense(s) sans justificatif</span><a href="#/operations?tab=depenses">Ouvrir</a></li>` : ""}
      ${!totalTodo ? `<li>${badge("valide")}<span>Tout est à jour.</span></li>` : ""}
    </ul></div>
    <p class="muted small" style="margin-top:18px">${o.sync.enabled ? `Synchronisation Qonto automatique active. Dernière synchronisation : ${o.sync.last_sync ? new Date(o.sync.last_sync).toLocaleString("fr-FR") : "jamais"}.` : "Synchronisation Qonto non configurée : <a href='#/parametres?tab=qonto'>configurer</a> ou importer un CSV dans Banque."}
    ${o.sync.last_error ? `<br><span class="neg">${esc(o.sync.last_error)}</span>` : ""}</p>
    <p class="muted small">Trésorerie et résultat sont différents : le résultat tient compte des factures non encaissées, des dettes et des amortissements ; la trésorerie est l'argent disponible.</p>`;
};
async function syncQonto(full = false) {
  toast("Synchronisation en cours…");
  const r = await run(() => post("/api/qonto/sync" + (full ? "?full=true" : "")));
  toast(`${r.imported} nouvelle(s) opération(s), ${r.duplicates} déjà connue(s), ${r.attachments} justificatif(s).`);
  if (r.attachments_remaining) { toast(`Récupération de ${r.attachments_remaining} justificatif(s) Qonto…`); await fetchQontoDocs(); return; }
  route();
}

// ------------------------------------------------------------------ banque
// ------------------------------------------------------------------ opérations (banque, dépenses, justificatifs, frais kilométriques)
// Champ de fichier en français, au style de l'application (remplace le bouton natif « Choose File »).
const fileField = (name, label = "Ajouter un justificatif", accept = ".pdf,image/*") => `<label class="file-field">
  <input type="file" name="${name}" accept="${accept}" class="sr-only" onchange="this.closest('.file-field').classList.toggle('has-file', !!this.files[0]); this.closest('.file-field').querySelector('.file-name').textContent = this.files[0] ? this.files[0].name : 'PDF, JPG ou PNG'">
  ${ic("upload")}<span class="file-btn">${label}</span><span class="file-name">PDF, JPG ou PNG</span></label>`;
// Bouton qui ajoute directement un justificatif à une opération ou une dépense.
const attachButton = (kind, id, small = false) => `<label class="attach-btn ${small ? "sm" : ""}" onclick="event.stopPropagation()" title="Ajouter le justificatif">
  <input type="file" accept=".pdf,image/*" class="sr-only" onchange="attachFile('${kind}', ${id}, this)">${ic("upload")}<span>${small ? "Ajouter" : "Ajouter le justificatif"}</span></label>`;
async function attachFile(kind, id, input) {
  if (!input.files[0]) return;
  const fd = new FormData(); fd.append("file", input.files[0]);
  await run(() => api(`/api/${kind === "tx" ? "transactions" : "expenses"}/${id}/document`, { method: "POST", body: fd }), "Justificatif ajouté");
  if ($("#drawer").classList.contains("open")) { kind === "tx" ? openTx(id) : openExpense(id); }
  refreshView();
}
// Justificatif d'une dépense : présent, déclaré perdu, non requis (relevé bancaire, IK, perso) ou à ajouter.
const expDocCell = (x) => x.document_id
  ? `<a class="doc-ok" href="/api/documents/${x.document_id}/file" target="_blank" onclick="event.stopPropagation()" title="Voir le justificatif">${ic("file")}</a>`
  : x.doc_status === "perdu" ? `<span class="badge plain" title="${esc(x.doc_note || "Justificatif perdu")}">Perdu</span>`
  : ["personnel", "ik"].includes(x.rule) ? ""
  : (x.code === "banque" && !Number(x.vat_cents)) ? `<span class="badge plain" title="Frais bancaire sans TVA : le relevé Qonto suffit">Relevé</span>`
  : `<span class="doc-actions">${attachButton("exp", x.id, true)}<button type="button" class="link-btn" onclick="event.stopPropagation();lostForm(${x.id})" title="Justificatif perdu">Perdu ?</button></span>`;
function lostForm(id) {
  openModal(`<h2 style="margin-top:0">Justificatif perdu</h2>
    <p class="small">La dépense reste déductible du résultat si elle est professionnelle et prouvée par le paiement. En revanche, <b>sans facture, la TVA ne peut pas être récupérée</b> (article 271 du CGI) : elle sera ajoutée au coût de la dépense. Si vous retrouvez la facture, ajoutez-la : la TVA redeviendra récupérable.</p>
    <p class="small muted">Conseil : demandez un duplicata au fournisseur (la plupart des factures se retéléchargent depuis leur espace client).</p>
    <form id="lostf"><div class="field"><label>Explication (conservée dans le dossier)</label><textarea name="note" rows="2" placeholder="Ex. ticket de caisse égaré, achat professionnel du 12 mars"></textarea></div>
    <label class="check"><input type="checkbox" name="drop_vat" checked> Ne pas récupérer la TVA de cette dépense (obligatoire sans facture)</label>
    <div class="toolbar" style="margin-top:14px"><button class="primary" type="submit">Confirmer</button><button type="button" class="ghost" onclick="closeModal()">Annuler</button></div></form>`);
  $("#lostf").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
    await run(() => post(`/api/expenses/${id}/lost`, { note: f.note, drop_vat: !!f.drop_vat }), "Justificatif déclaré perdu");
    closeModal(); if ($("#drawer").classList.contains("open")) openExpense(id); refreshView(); };
}
async function unlost(id) { await run(() => post(`/api/expenses/${id}/unlost`), "Justificatif de nouveau attendu"); openExpense(id); refreshView(); }
async function validateAllExpenses(n) {
  if (!confirm(`Valider les ${n} dépense(s) à valider ? Les écritures seront figées (une correction passera par une contre-passation).`)) return;
  const r = await run(() => post("/api/expenses/validate", {}));
  toast(`${r.validated} dépense(s) validée(s)`); r.errors.forEach((m) => toast(m, true)); refreshView();
}
const docCell = (t) => t.document_id
  ? `<a class="doc-ok" href="/api/documents/${t.document_id}/file" target="_blank" onclick="event.stopPropagation()" title="Voir le justificatif">${ic("file")}<span class="sr-only">Justificatif</span></a>`
  : t.doc_status === "perdu" ? `<span class="badge plain">Perdu</span>` : t.doc_status === "releve" ? `<span class="badge plain" title="Le relevé Qonto suffit">Relevé</span>`
  : (t.attachment_count ? `<span class="badge b-info" title="Justificatif présent dans Qonto, en cours de récupération">Qonto</span>` : (t.needs_document ? attachButton("tx", t.id, true) : ""));

const OPS_TABS = { a_traiter: "À traiter", toutes: "Toutes les opérations", depenses: "Dépenses et notes de frais", pieces: "Justificatifs déposés", km: "Frais kilométriques" };
ROUTES.operations = async (p) => {
  const tab = p.tab || "a_traiter";
  setActions(`<button class="primary" onclick="syncQonto()">${ic("sync")}Synchroniser Qonto</button>
    <button onclick="$('#up').click()">${ic("upload")}Déposer des justificatifs</button><input type="file" id="up" multiple accept=".pdf,image/*" hidden onchange="uploadDocs(this.files)">
    <button onclick="addMenu()">${ic("plus")}Ajouter</button>`);
  const counts = await api("/api/overview").catch(() => null);
  const n = counts ? counts.a_verifier : {};
  const badgeN = (k) => { const v = { a_traiter: n.operations, depenses: n.depenses, pieces: n.pieces_non_traitees }[k]; return v ? ` <span class="count">${v}</span>` : ""; };
  $("#view").innerHTML = `<div class="tabs" role="tablist">${Object.entries(OPS_TABS).map(([k, l]) => `<button role="tab" aria-selected="${k === tab}" class="${k === tab ? "active" : ""}" onclick="location.hash='#/operations?tab=${k}'">${l}${badgeN(k)}</button>`).join("")}</div><div id="tab"></div>`;
  if (tab === "a_traiter" || tab === "toutes") await renderTransactions(p, tab);
  else if (tab === "depenses") await renderExpenses(p);
  else if (tab === "pieces") await renderPieces();
  else if (tab === "km") await renderMileage(p);
  if (p.tx) openTx(Number(p.tx));
  if (p.id) openExpense(Number(p.id));
};
// Anciennes adresses (liens des contrôles) : redirigées vers la rubrique unique.
ROUTES.banque = (p) => ROUTES.operations({ ...p, tab: p.status === "a_verifier" ? "a_traiter" : (p.tx ? "toutes" : (p.tab || "toutes")) });
ROUTES.depenses = (p) => ROUTES.operations({ ...p, tab: p.tab || "depenses" });

function addMenu() {
  openModal(`<h2 style="margin-top:0">Ajouter</h2><div class="choice-list">
    <button onclick="closeModal();expenseForm()">${ic("card")}<span><b>Dépense ou facture fournisseur</b><small>Facture à payer, achat payé par la société</small></span></button>
    <button onclick="closeModal();expenseForm(null,'perso')">${ic("user")}<span><b>Note de frais payée personnellement</b><small>La société vous la doit (compte courant)</small></span></button>
    <button onclick="closeModal();location.hash='#/operations?tab=km'">${ic("route")}<span><b>Trajet professionnel</b><small>Frais kilométriques au barème</small></span></button>
    <button onclick="closeModal();$('#csv-input').click()">${ic("upload")}<span><b>Importer un relevé CSV</b><small>Export Qonto, en secours de la synchronisation</small></span></button>
    <button onclick="closeModal();manualTx()">${ic("bank")}<span><b>Saisir une opération bancaire</b><small>Seulement si elle n'est pas dans Qonto</small></span></button>
    <button onclick="closeModal();fetchQontoDocs()">${ic("download")}<span><b>Récupérer les justificatifs Qonto</b><small>Télécharge les pièces jointes déposées dans Qonto</small></span></button>
    <button onclick="closeModal();$('#reprise-input').click()">${ic("archive")}<span><b>Reprendre un dossier (Dougs)</b><small>Applique les catégories de votre ancien outil</small></span></button>
  </div><input type="file" id="csv-input" accept=".csv" hidden onchange="importCsv(this)"><input type="file" id="reprise-input" accept=".json,application/json" hidden onchange="repriseFile(this)">`);
}
/* Reprise d'un dossier Dougs : contrôle à blanc, puis catégorisation par lots (hébergement mutualisé). */
let REPRISE = null;
async function reprisePass(dry, validate) {
  const acc = { total: 0, classees: 0, deja_traitees: 0, a_verifier: [], introuvables: [], erreurs: [], trajets: 0, regles: 0 };
  let offset = 0;
  for (let i = 0; i < 60; i++) {
    const r = await run(() => post("/api/reprise", { data: REPRISE, offset, dry, validate }));
    acc.total = r.total; ["classees", "deja_traitees", "trajets", "regles"].forEach((k) => (acc[k] += r[k]));
    ["a_verifier", "introuvables", "erreurs"].forEach((k) => acc[k].push(...r[k]));
    if (r.next_offset === null || r.next_offset === undefined) break;
    offset = r.next_offset;
    if (!dry) toast(`Reprise en cours : ${offset} / ${r.total} opérations…`);
  }
  return acc;
}
function repriseList(title, rows, render) {
  if (!rows.length) return "";
  return `<details ${rows.length < 8 ? "open" : ""}><summary><b>${title}</b> (${rows.length})</summary><ul class="small" style="margin:8px 0 0;padding-left:18px">${rows.map((x) => `<li>${render(x)}</li>`).join("")}</ul></details>`;
}
async function repriseFile(input) {
  const f = input.files[0]; input.value = "";
  if (!f) return;
  try { REPRISE = JSON.parse(await f.text()); } catch (e) { toast("Fichier illisible : choisissez le fichier .json de reprise.", true); return; }
  toast("Contrôle du fichier…");
  const r = await reprisePass(true, true);
  openModal(`<h2 style="margin-top:0">Reprise depuis ${esc(REPRISE.source || "l'ancien logiciel")}</h2>
    <p class="muted small">${esc(REPRISE.company || "")} · ${r.total} opérations bancaires et ${r.trajets} trajets dans le fichier. Aucune opération déjà traitée ne sera modifiée.</p>
    <dl class="kv"><dt>À catégoriser</dt><dd><b>${r.classees}</b></dd><dt>Déjà traitées ici</dt><dd>${r.deja_traitees}</dd>
      <dt>À vérifier par vous</dt><dd>${r.a_verifier.length}</dd><dt>Introuvables dans Qonto</dt><dd>${r.introuvables.length}</dd><dt>Trajets</dt><dd>${r.trajets}</dd></dl>
    ${repriseList("Introuvables", r.introuvables, (x) => `${d(x.date)} · ${esc(x.label)} · ${eur(x.amount_cents)}`)}
    <label class="check" style="margin-top:12px"><input type="checkbox" id="reprise-validate" checked> Valider les écritures (elles l'étaient déjà dans ${esc(REPRISE.source || "l'ancien logiciel")})</label>
    <div class="toolbar" style="margin-top:16px"><button class="primary" onclick="repriseRun()">Lancer la reprise</button><button class="ghost" onclick="closeModal()">Annuler</button></div>`);
}
async function repriseRun() {
  const validate = $("#reprise-validate").checked;
  closeModal(); toast("Reprise lancée…");
  const r = await reprisePass(false, validate);
  openModal(`<h2 style="margin-top:0">Reprise terminée</h2>
    <dl class="kv"><dt>Opérations catégorisées</dt><dd><b>${r.classees}</b></dd><dt>Règles automatiques créées</dt><dd>${r.regles}</dd><dt>Trajets ajoutés</dt><dd>${r.trajets}</dd>
      <dt>Déjà traitées (inchangées)</dt><dd>${r.deja_traitees}</dd></dl>
    ${repriseList("À vérifier par vous", r.a_verifier, (x) => `${d(x.date)} · ${esc(x.label)} · ${eur(x.amount_cents)}<br><span class="muted">${esc(x.reason)}</span>`)}
    ${repriseList("Introuvables dans Qonto", r.introuvables, (x) => `${d(x.date)} · ${esc(x.label)} · ${eur(x.amount_cents)}`)}
    ${repriseList("Erreurs", r.erreurs, (x) => `${d(x.date)} · ${esc(x.label)} · ${eur(x.amount_cents)}<br><span class="muted">${esc(x.message)}</span>`)}
    <div class="toolbar" style="margin-top:16px"><button class="primary" onclick="closeModal();location.hash='#/operations'">Voir les opérations</button></div>`);
  refreshView();
}
async function fetchQontoDocs() {
  let total = 0;
  toast("Relecture de toutes les opérations Qonto…");
  for (let i = 0; i < 40; i++) {
    const r = await run(() => post("/api/qonto/attachments", i === 0 ? { full: true } : {}));
    total += r.fetched;
    toast(`${total} justificatif(s) récupéré(s)${r.remaining ? `, encore ${r.remaining}…` : "."}`);
    if (!r.remaining || (!r.fetched && i > 0)) break;
  }
  refreshView();
}

let SELECTED = new Set();
async function renderTransactions(p, tab) {
  const month = p.month || "";
  const status = tab === "a_traiter" ? "a_verifier" : (p.status || "");
  const [rows, groups] = await Promise.all([api(`/api/transactions?status=${status}&month=${month}&q=${encodeURIComponent(p.q || "")}`), tab === "a_traiter" ? api("/api/transactions/groups") : []]);
  SELECTED = new Set();
  const proposals = rows.filter((r) => r.status === "propose" && r.suggestion && r.suggestion.confidence >= 0.9).length;
  const sel = tab === "a_traiter";
  $("#tab").innerHTML = `
    ${groups.length ? `<h2 style="margin-top:4px">Opérations récurrentes</h2><p class="muted small" style="margin-top:-6px">Catégorisez en un clic tous les prélèvements d'un même fournisseur (abonnements, hébergement…). La règle s'appliquera aussi aux prochains.</p>
      <div class="group-grid">${groups.slice(0, 8).map((g, i) => `<div class="group-card"><div><b>${esc(g.name)}</b><div class="small muted">${g.count} opération(s) · ${eurSigned(g.total_cents)}</div></div>
        <button class="sm ${i === 0 ? "primary" : ""}" onclick='bulkForm(${JSON.stringify(g.ids)}, ${JSON.stringify(g.name).replace(/'/g, "&#39;")}, true, ${g.debit})'>Tout catégoriser</button></div>`).join("")}</div>` : ""}
    <div class="toolbar" style="margin-top:${groups.length ? 22 : 0}px">
      ${tab === "toutes" ? `<select style="width:auto" onchange="location.hash='#/operations?tab=toutes&status='+this.value"><option value="">Tous statuts</option><option value="rapproche" ${p.status === "rapproche" ? "selected" : ""}>Rapprochées</option><option value="a_traiter" ${p.status === "a_traiter" ? "selected" : ""}>À catégoriser</option></select>` : ""}
      <input type="month" value="${esc(month)}" style="width:auto" aria-label="Mois" onchange="location.hash='#/operations?tab=${tab}&month='+this.value">
      <input placeholder="Rechercher un libellé ou un montant" style="width:240px" value="${esc(p.q || "")}" aria-label="Rechercher" onkeydown="if(event.key==='Enter')location.hash='#/operations?tab=${tab}&q='+encodeURIComponent(this.value)">
      <span class="spacer"></span>
      ${proposals ? `<button class="accent" onclick="acceptAll()">${ic("check")}Accepter ${proposals} proposition(s) sûre(s)</button>` : ""}
    </div>
    <div class="bulk-bar" id="bulk-bar" hidden><span id="bulk-n"></span><span class="spacer"></span><button class="sm" onclick="SELECTED.clear();$$('.row-check').forEach(c=>c.checked=false);updateBulk()">Annuler</button><button class="sm primary" onclick="bulkForm([...SELECTED], null, false, null)">Catégoriser la sélection</button></div>
    <div class="table-wrap"><table>
      <thead><tr>${sel ? `<th style="width:36px"><input type="checkbox" aria-label="Tout sélectionner" onchange="$$('.row-check').forEach(c=>{c.checked=this.checked; c.checked?SELECTED.add(+c.value):SELECTED.delete(+c.value)});updateBulk()"></th>` : ""}<th>Date</th><th>Tiers</th><th>Libellé</th><th class="right">Montant</th><th>Pièce</th><th>${sel ? "Proposition" : "Affectation"}</th></tr></thead>
      <tbody>${rows.map((t) => `<tr class="click" tabindex="0" onclick="openTx(${t.id})">
        ${sel ? `<td onclick="event.stopPropagation()"><input type="checkbox" class="row-check" value="${t.id}" aria-label="Sélectionner" onchange="this.checked?SELECTED.add(${t.id}):SELECTED.delete(${t.id});updateBulk()"></td>` : ""}
        <td>${d(t.date)}</td><td>${esc(t.counterparty || "")}</td><td class="muted">${esc(t.label || "")}${t.note ? `<div class="small">${esc(t.note)}</div>` : ""}</td>
        <td>${eurSigned(t.amount_cents)}</td><td>${docCell(t)}</td>
        <td class="small">${t.allocations.length ? t.allocations.map((a) => esc(a.category || allocLabel(a))).join(", ") + " " + badge(t.status)
          : (t.suggestion ? `<span class="muted">${esc(t.suggestion.explanation)}</span>` : badge(t.status))}</td></tr>`).join("")}</tbody>
    </table>${rows.length ? "" : emptyState(tab === "a_traiter" ? "Tout est catégorisé." : "Aucune opération. Synchronisez Qonto ou importez un relevé CSV.", tab === "a_traiter" ? "check" : "bank")}</div>`;
}
function updateBulk() {
  const b = $("#bulk-bar"); if (!b) return;
  b.hidden = SELECTED.size === 0; $("#bulk-n").textContent = `${SELECTED.size} opération(s) sélectionnée(s)`;
}
async function bulkForm(ids, name, fromGroup, debit) {
  const cats = await categories();
  const rule = fromGroup ? null : null;
  openModal(`<h2 style="margin-top:0">Catégoriser ${ids.length} opération(s)${name ? ` · ${esc(name)}` : ""}</h2>
    <form id="bulkf">
      <div class="field"><label>Catégorie</label><select name="category_id">${cats.filter((c) => c.active && !c.is_asset && c.rule !== "ik").map((c) => `<option value="${c.id}" ${/heberg/.test(c.code) && /ionos|o2switch|ovh|hostinger/i.test(name || "") ? "selected" : ""}>${esc(c.name)}</option>`).join("")}</select></div>
      <div class="grid3"><div class="field"><label>TVA</label><select name="vat_regime">${Object.entries(REGIMES).map(([k, l]) => `<option value="${k}">${l}</option>`).join("")}</select></div>
        <div class="field"><label>Taux</label><select name="vat_rate">${["20", "10", "5.5", "2.1", "0"].map((r) => `<option>${r}</option>`).join("")}</select></div>
        <div class="field"><label>Nature</label><select name="nature"><option value="services">Services</option><option value="biens">Biens</option></select></div></div>
      <label class="check"><input type="checkbox" name="validate" checked> Valider directement ces dépenses</label>
      ${name ? `<label class="check"><input type="checkbox" name="remember" checked> Appliquer automatiquement aux prochaines opérations de ${esc(name)}</label>` : ""}
      ${debit === false ? `<p class="hint">Ces opérations sont des crédits : elles seront enregistrées comme remboursements du fournisseur (avoirs).</p>` : ""}
      <p class="hint">La TVA est recalculée à partir du taux choisi sur le montant de chaque prélèvement. Les justificatifs déjà présents dans Qonto sont rattachés automatiquement.</p>
      <div class="toolbar" style="margin-top:12px"><button class="primary" type="submit">${ic("check")}Catégoriser ${ids.length} opération(s)</button><button type="button" class="ghost" onclick="closeModal()">Annuler</button></div>
    </form>`);
  $("#bulkf").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target);
    const r = await run(() => post("/api/transactions/bulk", { ids, remember: !!f.remember, payload: { type: "depense_nouvelle", category_id: Number(f.category_id), vat_regime: f.vat_regime, vat_rate: f.vat_rate, nature: f.nature, validate: !!f.validate } }));
    toast(`${r.done} opération(s) catégorisée(s).`);
    r.errors.forEach((m) => toast(m, true));
    closeModal(); refreshView();
  };
}

async function renderExpenses(p) {
  const month = p.month || "";
  const rows = await api(`/api/expenses?month=${month}&status=${p.status || ""}&payment=${p.payment || ""}`);
  const total = rows.filter((r) => r.rule !== "personnel").reduce((a, r) => ({ ht: a.ht + r.ht_cents, ttc: a.ttc + r.ttc_cents }), { ht: 0, ttc: 0 });
  const pending = (await api("/api/expenses?status=a_valider")).filter((x) => x.category_id).length;
  $("#tab").innerHTML = `
    ${pending ? `<div class="bulk-bar" style="position:static;margin-bottom:12px"><span>${pending} dépense(s) catégorisée(s) à valider (abonnements, frais…)</span><button class="primary sm" onclick="validateAllExpenses(${pending})">${ic("check")}Tout valider</button></div>` : ""}
    <div class="toolbar">
      <select style="width:auto" aria-label="Paiement" onchange="location.hash='#/operations?tab=depenses&month=${month}&payment='+this.value"><option value="">Tous les paiements</option>${Object.entries(PAYMENT).map(([k, l]) => `<option value="${k}" ${p.payment === k ? "selected" : ""}>${l}</option>`).join("")}</select>
      <select style="width:auto" aria-label="Statut" onchange="location.hash='#/operations?tab=depenses&month=${month}&status='+this.value"><option value="">Tous statuts</option>${["a_valider", "propose_ia", "valide"].map((s) => `<option value="${s}" ${p.status === s ? "selected" : ""}>${STATUS[s][0]}</option>`).join("")}</select>
      <input type="month" style="width:auto" aria-label="Mois" value="${esc(month)}" onchange="location.hash='#/operations?tab=depenses&month='+this.value">
    </div>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Fournisseur</th><th>Libellé</th><th>Catégorie</th><th class="right">HT</th><th class="right">TVA</th><th class="right">TTC</th><th>Paiement</th><th>Pièce</th><th>Statut</th></tr></thead>
    <tbody>${rows.map((x) => `<tr class="click" tabindex="0" onclick="openExpense(${x.id})"><td>${d(x.date)}</td><td>${esc(x.supplier_name || "")}</td><td class="muted">${esc(x.label)}${x.ttc_cents < 0 ? ` <span class="badge b-info">Remboursement</span>` : ""}</td><td>${esc(x.category || "–")}</td>
      <td class="num">${eur(x.ht_cents)}</td><td class="num">${eur(x.vat_cents)}${x.vat_recovery < 100 && x.vat_cents ? ` <span class="badge plain" title="TVA non récupérable">NR</span>` : ""}</td><td class="num">${eur(x.ttc_cents)}</td>
      <td class="small">${PAYMENT[x.payment_method]}</td><td>${expDocCell(x)}</td><td>${badge(x.status)}</td></tr>`).join("")}</tbody>
    <tfoot><tr><td colspan="4">Total (hors dépenses personnelles)</td><td class="num">${eur(total.ht)}</td><td></td><td class="num">${eur(total.ttc)}</td><td colspan="3"></td></tr></tfoot></table>
    ${rows.length ? "" : emptyState("Aucune dépense sur cette période.", "card")}</div>`;
}

async function renderMileage(p) {
  const year = Number(p.year || new Date().getFullYear());
  const m = await api(`/api/mileage?year=${year}`);
  const v = m.vehicle;
  const kmf = (x10) => (x10 / 10).toLocaleString("fr-FR", { maximumFractionDigits: 1 });
  $("#tab").innerHTML = `
    <div class="cards">
      <div class="card hero"><div class="label">${ic("route")}Distance ${year}</div><div class="value">${kmf(m.km_x10)} km</div><div class="sub">${m.trips.length} trajet(s)</div></div>
      <div class="card"><div class="label">${ic("wallet")}Indemnités ${year}</div><div class="value">${eur(m.amount_cents)}</div><div class="sub">Dues par la société (compte courant)</div></div>
      <div class="card"><div class="label">${ic("scooter")}Véhicule</div><div class="value" style="font-size:18px">${esc(v.name)}</div><div class="sub">${esc(m.types[v.type] || "")} · ${esc((m.cv_options[v.type] || {})[v.cv] || "")} <a href="#" onclick="vehicleForm();return false">Modifier</a></div></div>
    </div>
    ${v.confirmed ? "" : `<div class="alert warn">${ic("alert")}<span>Vérifiez la puissance fiscale sur la carte grise (case P.6) de votre ${esc(v.name)} : elle détermine le barème. <a href="#" onclick="vehicleForm();return false">Confirmer le véhicule</a></span></div>`}
    <h2>Nouveau trajet professionnel</h2>
    <form id="kmf" class="panel panel-pad"><datalist id="places">${m.places.map((x) => `<option value="${esc(x)}">`).join("")}</datalist>
      <div class="grid4"><div class="field"><label>Date</label><input type="date" name="date" value="${today()}" required></div>
        <div class="field"><label>Départ</label><input name="from_place" list="places" placeholder="Meudon" required></div>
        <div class="field"><label>Arrivée</label><input name="to_place" list="places" placeholder="Client, ville" required></div>
        <div class="field"><label>Distance (km)</label><input name="km" inputmode="decimal" placeholder="12,5" required></div></div>
      <div class="grid2"><div class="field"><label>Motif professionnel</label><input name="purpose" placeholder="Rendez vous client Cabinet Durand" required></div>
        <div class="field" style="display:flex;align-items:flex-end"><label class="check"><input type="checkbox" name="round_trip" checked> Aller et retour (distance doublée)</label></div></div>
      <button class="primary" type="submit">${ic("plus")}Ajouter le trajet</button>
    </form>
    <details class="panel panel-pad" style="margin-top:12px"><summary><b>Saisir un total sur une période</b> <span class="muted small">(par exemple 2 000 km sur l'année)</span></summary>
      <p class="small" style="margin:10px 0">C'est possible, mais l'administration peut demander le détail : <b>date, trajet, distance et motif de chaque déplacement</b>. Gardez votre agenda (Google Agenda, rendez vous clients) qui le prouve. Un récapitulatif <b>mensuel</b> est plus sûr qu'un total annuel, et la société vous doit l'indemnité dès la fin de la période.</p>
      <form id="kmr"><div class="grid4"><div class="field"><label>Du</label><input type="date" name="from" value="${year}-01-01" required></div>
        <div class="field"><label>Au</label><input type="date" name="to" value="${today() < year + "-12-31" ? today() : year + "-12-31"}" required></div>
        <div class="field"><label>Total (km)</label><input name="km" inputmode="decimal" placeholder="2000" required></div>
        <div class="field"><label>Justification</label><input name="purpose" value="Rendez vous clients et prospects (détail dans l'agenda)" required></div></div>
        <button type="submit">${ic("plus")}Ajouter le récapitulatif</button></form></details>
    <div class="toolbar" style="margin-top:22px"><h2 style="margin:0">Trajets ${year}</h2><span class="spacer"></span>
      <select style="width:auto" aria-label="Année" onchange="location.hash='#/operations?tab=km&year='+this.value">${[year - 1, year, year + 1].map((y) => `<option ${y === year ? "selected" : ""}>${y}</option>`).join("")}</select>
      <a class="btn" href="/api/mileage/pdf?year=${year}" target="_blank">${ic("download")}État annuel (PDF)</a></div>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Trajet</th><th>Motif</th><th class="right">Km</th><th class="right">Indemnité</th><th></th></tr></thead>
      <tbody>${m.trips.map((t) => `<tr><td>${d(t.date)}</td><td>${esc(t.from_place)} ${t.round_trip ? "⇄" : "→"} ${esc(t.to_place)}</td><td class="muted">${esc(t.purpose)}</td>
        <td class="num">${kmf(t.km_x10 * (t.round_trip ? 2 : 1))}</td><td class="num">${eur(t.amount_cents)}</td>
        <td class="right"><button class="ghost icon sm" aria-label="Supprimer le trajet" onclick="delTrip(${t.id})">${ic("x")}</button></td></tr>`).join("")}</tbody>
      <tfoot><tr><td colspan="3">Total</td><td class="num">${kmf(m.km_x10)}</td><td class="num">${eur(m.amount_cents)}</td><td></td></tr></tfoot></table>
      ${m.trips.length ? "" : emptyState("Aucun trajet enregistré cette année.", "route")}</div>
    <div class="panel panel-pad" style="margin-top:16px"><h3 style="margin-top:0">Règles</h3><ul class="small muted" style="margin:0;padding-left:18px">${m.rules.map((r) => `<li>${esc(r)}</li>`).join("")}</ul></div>`;
  $("#kmr").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target);
    if (f.to < f.from) { toast("La date de fin précède la date de début.", true); return; }
    const r = await run(() => post("/api/mileage", { date: f.to, from_place: `Récapitulatif du ${d(f.from)} au ${d(f.to)}`, to_place: "Plusieurs déplacements professionnels", km: f.km, purpose: f.purpose }));
    toast(`Récapitulatif enregistré : ${eur(r.amount_cents)}.`); refreshView();
  };
  $("#kmf").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target);
    const r = await run(() => post("/api/mileage", f));
    toast(`Trajet enregistré : ${eur(r.amount_cents)}.`); refreshView();
  };
}
async function delTrip(id) { if (!confirm("Supprimer ce trajet ?")) return; await run(() => del(`/api/mileage/${id}`), "Trajet supprimé"); refreshView(); }
async function vehicleForm() {
  const m = await api("/api/mileage"); const v = m.vehicle;
  openModal(`<h2 style="margin-top:0">Véhicule personnel</h2><form id="vf">
    <div class="field"><label>Nom</label><input name="name" value="${esc(v.name)}"></div>
    <div class="grid2"><div class="field"><label>Type</label><select name="type" id="vtype">${Object.entries(m.types).map(([k, l]) => `<option value="${k}" ${v.type === k ? "selected" : ""}>${l}</option>`).join("")}</select></div>
      <div class="field"><label>Puissance fiscale (carte grise, case P.6)</label><select name="cv" id="vcv"></select></div></div>
    <p class="hint">Honda Forza 350 : scooter de plus de 50 cm³, catégorie « moto ». Indiquez la puissance inscrite sur votre carte grise.</p>
    <button class="primary">Enregistrer</button></form>`);
  const fill = () => { const t = $("#vtype").value; $("#vcv").innerHTML = Object.entries(m.cv_options[t]).map(([k, l]) => `<option value="${k}" ${v.cv === k ? "selected" : ""}>${l}</option>`).join(""); };
  $("#vtype").onchange = fill; fill();
  $("#vf").onsubmit = async (e) => { e.preventDefault(); await run(() => put("/api/settings/vehicle", { ...formData(e.target), confirmed: true }), "Véhicule enregistré"); closeModal(); refreshView(); };
}

const ALLOC = { vente: "Règlement client", depense: "Dépense", associe: "Compte courant associé", capital: "Capital", tva: "TVA", is: "Impôt sur les sociétés", virement_interne: "Virement interne", compte: "Compte" };
const allocLabel = (a) => (ALLOC[a.target_type] || a.target_type) + (a.account && a.target_type === "compte" ? " " + a.account : "");
async function importCsv(input) {
  const f = new FormData(); f.append("file", input.files[0]);
  const r = await run(() => api("/api/import/csv", { method: "POST", body: f }));
  toast(`${r.imported} opération(s) importée(s), ${r.duplicates} doublon(s) ignoré(s).`); route();
}
async function acceptAll() { const r = await run(() => post("/api/transactions/accept-all")); toast(`${r.accepted} opération(s) rapprochée(s).`); route(); }
function manualTx() {
  openModal(`<h2 style="margin-top:0">Saisir une opération bancaire</h2><form id="mtx">
    <div class="grid2"><div class="field"><label>Date</label><input type="date" name="date" value="${today()}"></div>
    <div class="field"><label>Montant (négatif pour un débit)</label><input name="amount" placeholder="-120,00"></div></div>
    <div class="field"><label>Tiers / libellé</label><input name="label"></div>
    <p class="hint">À utiliser seulement si l'opération n'est pas importée depuis Qonto (sinon doublon).</p>
    <button class="primary" type="submit">Enregistrer</button></form>`);
  $("#mtx").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
    await run(() => post("/api/transactions", { date: f.date, amount_cents: toCents(f.amount), label: f.label }), "Opération ajoutée"); closeModal(); route(); };
}

async function openTx(id) {
  const t = await api(`/api/transactions/${id}`);
  const [cats, invs, exps] = await Promise.all([categories(), api("/api/invoices"), api("/api/expenses?payment=a_payer")]);
  const s = t.suggestion;
  const credit = t.amount_cents > 0;
  openDrawer(`
    <h2>${esc(t.counterparty || t.label || "Opération")}</h2>
    <div class="cards" style="grid-template-columns:1fr 1fr 1fr"><div class="card"><div class="label">Montant</div><div class="value">${eurSigned(t.amount_cents)}</div></div>
      <div class="card"><div class="label">Date</div><div class="value" style="font-size:18px">${d(t.date)}</div></div>
      <div class="card"><div class="label">Statut</div><div style="margin-top:8px">${badge(t.status)}</div></div></div>
    <dl class="kv" style="margin-top:14px"><dt>Libellé</dt><dd>${esc(t.label || "")}</dd><dt>Type d'opération</dt><dd>${esc(t.operation_type || "")}</dd>
      <dt>Référence</dt><dd>${esc(t.reference || "")}</dd><dt>Source</dt><dd>${esc(t.source)} · <span class="mono small">${esc(t.external_id)}</span></dd>
      ${t.qonto_vat_cents ? `<dt>TVA indiquée dans Qonto</dt><dd>${eur(t.qonto_vat_cents)} (${esc(t.qonto_vat_rate || "")} %)</dd>` : ""}
      <dt>Justificatif</dt><dd>${t.documents.filter((x) => x.kind !== "alias").length ? t.documents.filter((x) => x.kind !== "alias").map(docLink).join(", ") : (t.amount_cents < 0 ? attachButton("tx", t.id) : `<span class="muted">Non requis pour un encaissement</span>`)}</dd></dl>
    ${s && t.status !== "rapproche" ? `<div class="alert ${s.confidence >= 0.9 ? "info" : "warn"}"><b>Proposition</b> (confiance ${Math.round(s.confidence * 100)} %) : ${esc(s.explanation)}
      <div style="margin-top:8px"><button class="primary sm" onclick="acceptTx(${t.id})">Accepter</button></div></div>` : ""}
    ${t.allocations.length ? `<h3>Affectation actuelle</h3><div class="panel panel-pad">${t.allocations.map((a) => `<div>${esc(allocLabel(a))} : ${eur(a.amount_cents)}
      ${a.target && a.target_type === "depense" ? ` · <a href="#/depenses?id=${a.target.id}">${esc(a.target.supplier_name || "")} ${esc(a.target.label)}</a>` : ""}
      ${a.target && a.target_type === "vente" ? ` · <a href="#/recettes?id=${a.target.id}">Facture ${esc(a.target.number)}</a>` : ""}</div>`).join("")}</div>` : ""}
    <h3>${t.allocations.length ? "Modifier l'affectation" : "Catégoriser"}</h3>
    <form id="classify" class="panel panel-pad">
      <div class="field"><label>Nature de l'opération</label><select name="type" id="ctype">
        ${credit ? `<option value="vente">Règlement d'une facture client</option><option value="remboursement_fournisseur">Remboursement d'un fournisseur (abonnement, avoir)</option><option value="associe">Apport personnel en compte courant</option>
          <option value="capital">Libération du capital social</option><option value="tva">Remboursement de crédit de TVA</option><option value="compte">Autre (compte comptable)</option>`
        : `<option value="depense_nouvelle">Dépense (nouvelle)</option><option value="depense">Paiement d'une facture fournisseur déjà saisie</option>
          <option value="associe">Remboursement de mon compte courant</option><option value="tva">Paiement de TVA</option><option value="is">Impôt sur les sociétés</option>
          <option value="compte">Autre (compte comptable)</option>`}
        <option value="virement_interne">Virement entre comptes de la société</option><option value="doublon">Doublon (à ignorer)</option></select></div>
      <div id="cfields"></div>
      <button class="primary" type="submit">Enregistrer</button>
    </form>
    <h3>Écritures comptables</h3>${entriesHtml(t.entries)}`);
  const sel = $("#ctype");
  if (s && [...sel.options].some((o) => o.value === s.type)) sel.value = s.type;
  else if (t.allocations[0]) sel.value = t.allocations[0].target_type === "depense" ? "depense_nouvelle" : t.allocations[0].target_type;
  const render = () => {
    const v = sel.value; let h = "";
    if (v === "depense_nouvelle" || v === "remboursement_fournisseur") h = `${v === "remboursement_fournisseur" ? `<div class="alert info">${ic("info")}<span>Le fournisseur vous rembourse une dépense (abonnement résilié, avoir) : la charge et la TVA déductible sont diminuées d'autant. Choisissez la catégorie de la dépense d'origine.</span></div>` : ""}
      <div class="grid2"><div class="field"><label>Catégorie</label><select name="category_id">${cats.filter((c) => c.active).map((c) => `<option value="${c.id}" ${s && s.category_id == c.id ? "selected" : ""}>${esc(c.name)}</option>`).join("")}</select></div>
      <div class="field"><label>Fournisseur</label><input name="supplier_name" value="${esc(t.counterparty || "")}"></div></div>
      <div class="field"><label>Libellé</label><input name="label" value="${esc(t.label || "")}"></div>
      <div class="grid3"><div class="field"><label>TVA</label><select name="vat_regime">${Object.entries(REGIMES).map(([k, l]) => `<option value="${k}" ${s && s.vat_regime === k ? "selected" : ""}>${l}</option>`).join("")}</select></div>
      <div class="field"><label>Taux</label><select name="vat_rate">${["20", "10", "5.5", "2.1", "0"].map((r) => `<option ${t.qonto_vat_rate === r ? "selected" : ""}>${r}</option>`).join("")}</select></div>
      <div class="field"><label>Montant TVA (facultatif)</label><input name="vat" value="${centsToInput(t.qonto_vat_cents)}" placeholder="calculé"></div></div>
      <div class="grid2"><div class="field"><label>Nature</label><select name="nature"><option value="services">Services</option><option value="biens">Biens</option></select></div>
      <div class="field"><label>Justificatif</label>${t.documents.filter((x) => x.kind !== "alias").length ? `<div class="muted small" style="padding-top:10px">${ic("file")} Justificatif de l'opération repris automatiquement</div>` : fileField("file")}</div></div>
      <div class="field"><label>Commentaire (objet, invités, bénéficiaire…)</label><input name="comment"></div>`;
    else if (v === "depense") h = `<div class="field"><label>Facture fournisseur à payer</label><select name="expense_id">${exps.map((x) => `<option value="${x.id}">${d(x.date)} · ${esc(x.supplier_name || "")} · ${eur(x.ttc_cents)}</option>`).join("")}</select>
      ${exps.length ? "" : `<p class="hint">Aucune dépense « à payer ». Utilisez « Dépense (nouvelle) ».</p>`}</div>`;
    else if (v === "vente") { const open = invs.filter((i) => i.kind === "facture" && i.remaining_cents > 0);
      h = `<div class="field"><label>Facture client</label><select name="invoice_id">${open.map((i) => `<option value="${i.id}" ${s && s.invoice_id == i.id ? "selected" : ""}>${esc(i.number)} · ${esc(i.client)} · reste ${eur(i.remaining_cents)}</option>`).join("")}</select></div>
      <div class="grid2"><div class="field"><label>Commission de la plateforme de paiement</label><input name="commission" value="${centsToInput(s && s.commission_cents ? s.commission_cents : null)}" placeholder="0,00"><div class="hint">Retenue par SumUp, Stripe… : la facture est soldée pour son montant total, la commission est enregistrée en frais bancaires. La TVA reste due sur le total facturé.</div></div>
      <div class="field"><label>Plateforme</label><input name="provider" value="${esc(/sumup|stripe|paypal|payplug|lydia|qonto/i.test(t.counterparty || "") ? t.counterparty : "")}" placeholder="SumUp, Stripe…"></div></div>`; }
    else if (v === "compte") h = `<div class="field"><label>Compte</label><input name="account" list="acc-list" placeholder="ex. 775000"><datalist id="acc-list"></datalist></div>`;
    else if (v === "associe") h = `<div class="alert info">${credit ? "Apport : la société vous doit cette somme (compte 455). Ce n'est pas un chiffre d'affaires." : "Remboursement : diminue la somme que la société vous doit. Ce n'est ni une charge ni une rémunération."}</div><div class="field"><label>Commentaire</label><input name="comment"></div>`;
    else if (v === "capital") h = `<div class="alert info">Libération du capital social (compte 1013), distincte d'un apport en compte courant.</div>`;
    else if (v === "doublon") h = `<div class="field"><label>Motif</label><input name="comment" value="Doublon"></div>`;
    $("#cfields").innerHTML = h;
    if (v === "compte") accountsList().then((a) => { $("#acc-list").innerHTML = a.map((x) => `<option value="${x.number}">${esc(x.label)}</option>`).join(""); });
  };
  sel.onchange = render; render();
  $("#classify").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target); const payload = { type: f.type };
    if (f.type === "depense_nouvelle" || f.type === "remboursement_fournisseur") {
      let doc = null;
      const fi = e.target.querySelector("[name=file]"); const file = fi && fi.files[0];
      if (file) { const fd = new FormData(); fd.append("file", file); fd.append("process", "false"); doc = (await run(() => api("/api/documents", { method: "POST", body: fd }))).document_id; }
      Object.assign(payload, { category_id: Number(f.category_id), supplier_name: f.supplier_name, label: f.label, vat_regime: f.vat_regime,
        vat_rate: f.vat_rate, nature: f.nature, comment: f.comment, document_id: doc });
      if (f.vat) payload.vat_cents = toCents(f.vat);
    }
    if (f.type === "depense") payload.expense_id = Number(f.expense_id);
    if (f.type === "vente") { payload.invoice_id = Number(f.invoice_id); const c = toCents(f.commission); if (c) payload.commission_cents = c; payload.provider = f.provider; }
    if (f.type === "compte") payload.account = f.account;
    if (f.comment && !["depense_nouvelle", "remboursement_fournisseur"].includes(f.type)) payload.comment = f.comment;
    await run(() => post(`/api/transactions/${id}/classify`, payload), "Opération enregistrée");
    openTx(id); refreshView();
  };
}
async function acceptTx(id) { await run(() => post(`/api/transactions/${id}/accept`), "Proposition acceptée"); openTx(id); refreshView(); }
function refreshView() { const y = window.scrollY; route(true).then(() => window.scrollTo(0, y)); }

// ------------------------------------------------------------------ recettes
ROUTES.recettes = async (p) => {
  setActions(`<button class="primary" onclick="invoiceForm()">${ic("plus")}Nouvelle facture</button>`);
  const rows = await api("/api/invoices");
  const tot = rows.filter((r) => r.kind === "facture").reduce((a, r) => a + r.remaining_cents, 0);
  $("#view").innerHTML = `
    <p class="muted">Une facture émise n'est pas un encaissement : elle devient « payée » lorsqu'un virement Qonto lui est rapproché. Reste à encaisser : <b>${eur(tot)}</b>.</p>
    <div class="table-wrap"><table><thead><tr><th>Numéro</th><th>Date</th><th>Client</th><th class="right">HT</th><th class="right">TVA</th><th class="right">TTC</th><th class="right">Reste dû</th><th>Statut</th></tr></thead>
    <tbody>${rows.map((r) => `<tr class="click" tabindex="0" onclick="openInvoice(${r.id})"><td class="mono">${esc(r.number)}${r.kind === "avoir" ? ` <span class="badge b-info">Avoir</span>` : ""}</td><td>${d(r.issue_date)}</td><td>${esc(r.client)}</td>
      <td class="num">${eur(r.kind === "avoir" ? -r.ht_cents : r.ht_cents)}</td><td class="num">${eur(r.kind === "avoir" ? -r.vat_cents : r.vat_cents)}</td><td class="num">${eur(r.kind === "avoir" ? -r.ttc_cents : r.ttc_cents)}</td>
      <td class="num">${r.kind === "facture" ? eur(r.remaining_cents) : ""}</td><td>${badge(r.payment_status)}</td></tr>`).join("")}</tbody></table>
    ${rows.length ? "" : emptyState("Aucune facture. Créez votre première facture ou importez un PDF.", "invoice")}</div>`;
  if (p.id) openInvoice(Number(p.id));
};
async function invoiceForm(credited) {
  const [clients, next] = await Promise.all([api("/api/parties?kind=client"), api(`/api/invoices/next-number?kind=${credited ? "avoir" : "facture"}`)]);
  const orig = credited ? await api(`/api/invoices/${credited}`) : null;
  openDrawer(`<h2>${credited ? "Nouvel avoir sur " + esc(orig.number) : "Nouvelle facture client"}</h2>
    <form id="invf">
      ${credited ? `<p class="muted">Client : ${esc(orig.client)} · facture de ${eur(orig.ttc_cents)} TTC</p>` : `
      <div class="grid2"><div class="field"><label>Client</label><input name="client_name" list="clients" required><datalist id="clients">${clients.map((c) => `<option value="${esc(c.name)}">`).join("")}</datalist></div>
      <div class="field"><label>N° TVA du client (UE)</label><input name="client_vat_number"></div></div>`}
      <div class="grid3"><div class="field"><label>Numéro</label><input name="number" value="${esc(next.number)}"><div class="hint">Numérotation continue proposée</div></div>
        <div class="field"><label>Date d'émission</label><input type="date" name="issue_date" value="${today()}"></div>
        <div class="field"><label>Échéance</label><input type="date" name="due_date"></div></div>
      ${credited ? "" : `<div class="grid2"><div class="field"><label>Nature</label><select name="nature"><option value="services">Prestations de services (TVA à l'encaissement)</option><option value="biens">Ventes de biens (TVA à la facturation)</option></select></div>
        <div class="field"><label>Régime de TVA</label><select name="vat_regime"><option value="fr">TVA française</option><option value="autoliquidation_ue">Client professionnel UE (autoliquidation)</option><option value="export">Client hors UE</option><option value="exonere">Exonérée</option></select></div></div>`}
      <h3>Lignes</h3>
      <table class="lines-table"><thead><tr><th>Description</th><th style="width:70px">Qté</th><th style="width:120px">Prix unitaire HT</th><th style="width:80px">TVA %</th><th></th></tr></thead><tbody id="ilines"></tbody></table>
      <button type="button" class="sm" style="margin:8px 0" onclick="addInvLine()">Ajouter une ligne</button>
      <div class="field"><label>PDF de la facture (facultatif)</label>${fileField("file", "Joindre le PDF", ".pdf")}</div>
      <div class="field"><label>Notes</label><input name="notes"></div>
      <div id="invtot" class="muted"></div>
      <button class="primary" type="submit">${credited ? "Émettre l'avoir" : "Enregistrer la facture"}</button>
    </form>`);
  addInvLine(credited ? { description: "Avoir sur facture " + orig.number, price: "" } : {});
  $("#invf").addEventListener("input", invTotals);
  $("#invf").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target);
    const lines = $$("#ilines tr").map((tr) => ({ description: $("[data-k=desc]", tr).value, quantity: $("[data-k=qty]", tr).value.replace(",", "."),
      unit_price_cents: toCents($("[data-k=price]", tr).value), vat_rate: $("[data-k=rate]", tr).value })).filter((l) => l.unit_price_cents);
    let document_id = null; const file = e.target.querySelector("[name=file]").files[0];
    if (file) { const fd = new FormData(); fd.append("file", file); fd.append("kind", "facture-client"); fd.append("process", "false"); document_id = (await api("/api/documents", { method: "POST", body: fd })).document_id; }
    const body = { ...f, lines, document_id, kind: credited ? "avoir" : "facture", credited_invoice_id: credited || null };
    delete body.file;
    const r = await run(() => post("/api/invoices", body), credited ? "Avoir émis" : "Facture enregistrée");
    (r.warnings || []).forEach((w) => toast(w, true));
    closeDrawer(); route();
  };
}
function addInvLine(l = {}) {
  const tr = document.createElement("tr");
  tr.innerHTML = `<td><input data-k="desc" value="${esc(l.description || "")}"></td><td><input data-k="qty" value="1"></td><td><input data-k="price" value="${esc(l.price || "")}"></td>
    <td><select data-k="rate">${["20", "10", "5.5", "2.1", "0"].map((r) => `<option>${r}</option>`).join("")}</select></td><td><button type="button" class="ghost sm" aria-label="Supprimer la ligne" onclick="this.closest('tr').remove();invTotals()">${ic("x")}</button></td>`;
  $("#ilines").appendChild(tr);
}
function invTotals() {
  let ht = 0, tva = 0;
  $$("#ilines tr").forEach((tr) => { try { const p = toCents($("[data-k=price]", tr).value) || 0; const q = Number($("[data-k=qty]", tr).value.replace(",", ".")) || 0; const h = Math.round(p * q); ht += h; tva += Math.round(h * Number($("[data-k=rate]", tr).value) / 100); } catch (e) { /* */ } });
  $("#invtot").innerHTML = `Total HT ${eur(ht)} · TVA ${eur(tva)} · <b>TTC ${eur(ht + tva)}</b>`;
}
async function openInvoice(id) {
  const r = await api(`/api/invoices/${id}`);
  openDrawer(`<h2>${r.kind === "avoir" ? "Avoir" : "Facture"} ${esc(r.number)}</h2>
    <div style="margin-bottom:12px">${badge(r.payment_status)} ${r.kind === "facture" && r.payment_status !== "payee" ? `<span class="muted small">Reste dû ${eur(r.remaining_cents)}</span>` : ""}</div>
    <dl class="kv"><dt>Client</dt><dd>${esc(r.client)} ${r.client_vat ? `<span class="muted">(${esc(r.client_vat)})</span>` : ""}</dd><dt>Date</dt><dd>${d(r.issue_date)}</dd><dt>Échéance</dt><dd>${d(r.due_date)}</dd>
      <dt>Nature</dt><dd>${r.nature === "services" ? "Prestations de services" : "Ventes de biens"} · ${esc(r.vat_regime)}</dd>
      <dt>Montants</dt><dd>HT ${eur(r.ht_cents)} · TVA ${eur(r.vat_cents)} · TTC <b>${eur(r.ttc_cents)}</b></dd>
      <dt>PDF</dt><dd>${r.document_id ? `<a href="/api/documents/${r.document_id}/file" target="_blank">Ouvrir</a>` : `<span class="muted">Non archivé</span>`}</dd></dl>
    <h3>Lignes</h3><div class="table-wrap"><table>${r.lines.map((l) => `<tr><td>${esc(l.description)}</td><td class="num">${esc(l.quantity)} × ${eur(l.unit_price_cents)}</td><td class="num">${l.vat_rate} %</td><td class="num">${eur(l.ht_cents)}</td></tr>`).join("")}</table></div>
    <h3>Encaissements</h3>${r.payments.length ? `<div class="panel panel-pad">${r.payments.map((p) => `<div>${d(p.date)} · ${eur(p.amount_cents)} · <a href="#/banque?tx=${p.bank_transaction_id}" onclick="closeDrawer()">${esc(p.label || "opération")}</a></div>`).join("")}</div>` : `<p class="muted small">Aucun encaissement rapproché. Rapprochez le virement dans Banque.</p>`}
    ${r.credit_notes.length ? `<h3>Avoirs</h3>${r.credit_notes.map((c) => `<div><a href="#/recettes?id=${c.id}">${esc(c.number)}</a> · ${eur(c.ttc_cents)}</div>`).join("")}` : ""}
    <div class="toolbar" style="margin-top:16px">${r.kind === "facture" ? `<button onclick="invoiceForm(${r.id})">Émettre un avoir</button>` : ""}</div>
    <h3>Écritures comptables</h3>${entriesHtml(r.entries)}
    <p class="hint">Une facture émise ne se modifie pas et ne se supprime pas : la correction passe par un avoir.</p>`);
}

// ------------------------------------------------------------------ dépenses
function setupDrop() {
  const z = $("#dropzone"); if (!z) return;
  z.onclick = () => $("#up").click();
  z.onkeydown = (e) => { if (e.key === "Enter" || e.key === " ") { e.preventDefault(); $("#up").click(); } };
  z.ondragover = (e) => { e.preventDefault(); z.classList.add("over"); };
  z.ondragleave = () => z.classList.remove("over");
  z.ondrop = (e) => { e.preventDefault(); z.classList.remove("over"); uploadDocs(e.dataTransfer.files); };
}
async function uploadDocs(files) {
  const results = [];
  for (const f of files) {
    toast(`Lecture de ${f.name}…`);
    const fd = new FormData(); fd.append("file", f);
    try { results.push({ name: f.name, ...(await api("/api/documents", { method: "POST", body: fd })) }); } catch (e) { toast(`${f.name} : ${e.message}`, true); }
  }
  const auto = results.filter((r) => r.status === "rattache").length;
  toast(`${results.length} justificatif(s) déposé(s), ${auto} rattaché(s) automatiquement.`);
  location.hash = "#/operations?tab=pieces";
  route();
}
async function renderPieces() {
  const docs = await api("/api/documents?unlinked=true");
  $("#tab").innerHTML = `
    <div id="dropzone" class="drop" role="button" tabindex="0" aria-label="Déposer des justificatifs">${ic("upload")}<strong>Déposez vos factures et tickets</strong><span class="small">PDF, JPG ou PNG, ou prenez une photo sur téléphone : lecture automatique et rapprochement avec Qonto.</span></div>
    <p class="muted small" style="margin-top:14px">Justificatifs déposés ici et pas encore rattachés à une opération. Les pièces jointes dans Qonto sont rattachées automatiquement à leur opération. Les données lues par l'IA sont des propositions : vérifiez-les avant de valider.</p>
    <div class="table-wrap"><table><thead><tr><th>Fichier</th><th>Fournisseur</th><th>Date</th><th class="right">TTC</th><th>Lecture</th><th></th></tr></thead>
    <tbody>${docs.map((x) => `<tr><td><a href="/api/documents/${x.id}/file" target="_blank">${esc(x.original_name)}</a></td><td>${esc(x.party_name || "")}</td><td>${d(x.doc_date)}</td>
      <td class="num">${x.amount_cents ? eur(x.amount_cents) : ""}</td><td>${x.ai_data ? `<span class="badge ${x.ai_data.source === "facturx" ? "b-ok" : "b-ai"}">${x.ai_data.source === "facturx" ? "Factur-X" : "IA"}</span>` : `<span class="badge">${x.ai_status === "manuel" ? "Saisie manuelle" : "Non lu"}</span>`}</td>
      <td class="right"><button class="sm" onclick="openPiece(${x.id})">Traiter</button></td></tr>`).join("")}</tbody></table>
    ${docs.length ? "" : emptyState("Aucun justificatif en attente.", "file")}</div>`;
  setupDrop();
}
async function openPiece(id) {
  const docs = await api("/api/documents"); const doc = docs.find((x) => x.id === id);
  let ex = doc.ai_data; let matches = [];
  if (!ex) {
    try { const r = await post(`/api/documents/${id}/process`); ex = r.extraction; matches = r.matches || []; if (r.status === "manuel") toast(r.message); } catch (e) { toast(e.message, true); }
  } else {
    try { const r = await post(`/api/documents/${id}/process`); ex = r.extraction || ex; matches = r.matches || []; } catch (e) { /* lecture déjà faite */ }
  }
  ex = ex || {};
  const exps = (await api("/api/expenses")).filter((x) => !x.document_id).slice(0, 80);
  const cats = await categories();
  openDrawer(`<h2>${esc(doc.original_name)}</h2>
    <p><a href="/api/documents/${id}/file" target="_blank">Ouvrir le document</a> ${ex.source ? `<span class="badge ${ex.source === "facturx" ? "b-ok" : "b-ai"}">${ex.source === "facturx" ? "Lu depuis la facture électronique" : "Proposé par l'IA"}</span>` : ""}</p>
    ${(ex.anomalies || []).length ? `<div class="alert warn"><b>Points d'attention</b><ul style="margin:4px 0 0 18px;padding:0">${ex.anomalies.map((a) => `<li>${esc(a)}</li>`).join("")}</ul></div>` : ""}
    <form id="pf" class="panel panel-pad">
      <div class="grid2"><div class="field"><label>Fournisseur</label><input name="supplier_name" value="${esc(ex.supplier_name || "")}"></div>
        <div class="field"><label>Date</label><input type="date" name="date" value="${esc(ex.date || today())}"></div></div>
      <div class="grid2"><div class="field"><label>Libellé</label><input name="label" value="${esc(ex.description || "")}"></div>
        <div class="field"><label>N° de facture</label><input name="invoice_number" value="${esc(ex.invoice_number || "")}"></div></div>
      <div class="grid4"><div class="field"><label>HT</label><input name="ht" value="${centsToInput(ex.ht_cents)}"></div><div class="field"><label>TVA</label><input name="vat" value="${centsToInput(ex.vat_cents)}"></div>
        <div class="field"><label>TTC</label><input name="ttc" value="${centsToInput(ex.ttc_cents)}"></div><div class="field"><label>Taux</label><select name="vat_rate">${["20", "10", "5.5", "2.1", "0"].map((r) => `<option ${ex.vat_rate === r ? "selected" : ""}>${r}</option>`).join("")}</select></div></div>
      <div class="grid3"><div class="field"><label>Catégorie</label><select name="category_id">${cats.filter((c) => c.active).map((c) => `<option value="${c.id}" ${ex.category_id == c.id ? "selected" : ""}>${esc(c.name)}</option>`).join("")}</select></div>
        <div class="field"><label>TVA</label><select name="vat_regime">${Object.entries(REGIMES).map(([k, l]) => `<option value="${k}" ${ex.vat_regime === k ? "selected" : ""}>${l}</option>`).join("")}</select></div>
        <div class="field"><label>Nature</label><select name="nature"><option value="services">Services</option><option value="biens" ${ex.nature === "biens" ? "selected" : ""}>Biens</option></select></div></div>
      <h3>Rattacher à</h3>
      ${matches.map((m, i) => `<label class="check"><input type="radio" name="target" value="tx:${m.transaction.id}" ${i === 0 ? "checked" : ""}> Opération Qonto du ${d(m.transaction.date)} · ${esc(m.transaction.counterparty || "")} · ${eur(m.transaction.amount_cents)} <span class="badge b-info">${Math.round(m.score * 100)} %</span></label>`).join("")}
      ${exps.length ? `<label class="check"><input type="radio" name="target" value="exp" ${matches.length ? "" : ""}> Dépense déjà saisie sans justificatif : <select name="expense_id" style="width:auto;margin-left:6px">${exps.map((x) => `<option value="${x.id}">${d(x.date)} ${esc(x.supplier_name || "")} ${eur(x.ttc_cents)}</option>`).join("")}</select></label>` : ""}
      <label class="check"><input type="radio" name="target" value="new" ${matches.length ? "" : "checked"}> Nouvelle dépense non encore payée par Qonto
        <select name="payment_method" style="width:auto;margin-left:6px"><option value="perso">payée personnellement</option><option value="a_payer">à payer</option></select></label>
      <div style="margin-top:14px"><button class="primary" type="submit">Valider la proposition</button></div>
    </form>`);
  $("#pf").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target); const target = $("input[name=target]:checked", e.target)?.value || "new";
    const expense = { supplier_name: f.supplier_name, label: f.label, invoice_number: f.invoice_number, category_id: Number(f.category_id), vat_regime: f.vat_regime,
      vat_rate: f.vat_rate, nature: f.nature, ht_cents: toCents(f.ht), vat_cents: toCents(f.vat), ttc_cents: toCents(f.ttc), date: f.date };
    let body;
    if (target.startsWith("tx:")) { body = { action: "transaction", transaction_id: Number(target.slice(3)), expense: { ...expense, ht_cents: undefined, ttc_cents: undefined } }; }
    else if (target === "exp") body = { action: "expense", expense_id: Number(f.expense_id) };
    else body = { action: "new_expense", expense: { ...expense, payment_method: f.payment_method, status: "a_valider", source: ex.source === "ia" ? "ia" : "manuel" } };
    await run(() => post(`/api/documents/${id}/confirm`, body), "Justificatif traité");
    closeDrawer(); route();
  };
}
async function expenseForm(x, mode) {
  const cats = await categories();
  x = x || { date: today(), payment_method: mode === "perso" ? "perso" : "a_payer", vat_regime: "fr", vat_rate: "20", nature: "services" };
  const edit = !!x.id;
  const html = `<h2>${edit ? "Modifier la dépense" : mode === "perso" ? "Dépense payée personnellement" : "Nouvelle dépense"}</h2>
    ${mode === "perso" ? `<div class="alert info">La dépense est enregistrée en charge et le montant est inscrit à votre crédit en compte courant d'associé (la société vous le doit). Son remboursement ultérieur ne sera pas compté une seconde fois.</div>` : ""}
    <form id="xf">
      <div class="grid2"><div class="field"><label>Date</label><input type="date" name="date" value="${esc(x.date)}"></div><div class="field"><label>Fournisseur</label><input name="supplier_name" value="${esc(x.supplier_name || "")}"></div></div>
      <div class="grid2"><div class="field"><label>Libellé</label><input name="label" value="${esc(x.label || "")}"></div><div class="field"><label>N° de facture</label><input name="invoice_number" value="${esc(x.invoice_number || "")}"></div></div>
      <div class="grid4"><div class="field"><label>HT</label><input name="ht" value="${edit ? centsToInput(x.ht_cents) : ""}"></div><div class="field"><label>TVA</label><input name="vat" value="${edit ? centsToInput(x.vat_cents) : ""}"></div>
        <div class="field"><label>TTC</label><input name="ttc" value="${edit ? centsToInput(x.ttc_cents) : ""}"></div><div class="field"><label>Taux</label><select name="vat_rate">${["20", "10", "5.5", "2.1", "0"].map((r) => `<option ${String(x.vat_rate) === r ? "selected" : ""}>${r}</option>`).join("")}</select></div></div>
      <p class="hint" style="margin-top:-6px">Renseignez le TTC et le taux : HT et TVA sont calculés. Ou saisissez les montants exacts de la facture.</p>
      <div class="grid3"><div class="field"><label>Catégorie</label><select name="category_id"><option value="">Choisir…</option>${cats.filter((c) => c.active || c.id === x.category_id).map((c) => `<option value="${c.id}" ${x.category_id === c.id ? "selected" : ""}>${esc(c.name)}</option>`).join("")}</select></div>
        <div class="field"><label>TVA</label><select name="vat_regime">${Object.entries(REGIMES).map(([k, l]) => `<option value="${k}" ${x.vat_regime === k ? "selected" : ""}>${l}</option>`).join("")}</select></div>
        <div class="field"><label>Nature</label><select name="nature"><option value="services">Services</option><option value="biens" ${x.nature === "biens" ? "selected" : ""}>Biens</option></select></div></div>
      <div class="grid3"><div class="field"><label>Paiement</label><select name="payment_method">${Object.entries(PAYMENT).map(([k, l]) => `<option value="${k}" ${x.payment_method === k ? "selected" : ""}>${l}</option>`).join("")}</select></div>
        <div class="field"><label>TVA récupérable %</label><input name="vat_recovery" type="number" min="0" max="100" value="${x.vat_recovery ?? ""}" placeholder="selon catégorie"></div>
        <div class="field"><label>Charge déductible %</label><input name="deductible" type="number" min="0" max="100" value="${x.deductible ?? ""}" placeholder="selon catégorie"></div></div>
      <div class="grid2"><div class="field"><label>Bénéficiaire (cadeaux)</label><input name="beneficiary" value="${esc(x.beneficiary || "")}"></div>
        <div class="field"><label>Justificatif ${x.document_id ? "(remplacer)" : ""}</label>${fileField("file", x.document_id ? "Remplacer le justificatif" : "Ajouter un justificatif")}</div></div>
      <div class="field"><label>Commentaire</label><textarea name="comment" rows="2">${esc(x.comment || "")}</textarea></div>
      ${edit && x.status === "valide" ? `<div class="field"><label>Motif de la correction (dépense validée : la modification sera tracée par contrepassation)</label><input name="reason" required></div>` : ""}
      <button class="primary" type="submit">Enregistrer</button>
    </form>`;
  openDrawer(html);
  $("#xf").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target);
    let document_id = x.document_id || null; const file = e.target.querySelector("[name=file]").files[0];
    if (file) { const fd = new FormData(); fd.append("file", file); fd.append("process", "false"); document_id = (await api("/api/documents", { method: "POST", body: fd })).document_id; }
    const body = { date: f.date, supplier_name: f.supplier_name, label: f.label, invoice_number: f.invoice_number, vat_rate: f.vat_rate, vat_regime: f.vat_regime,
      nature: f.nature, payment_method: f.payment_method, category_id: f.category_id ? Number(f.category_id) : null, beneficiary: f.beneficiary, comment: f.comment, document_id };
    const ht = toCents(f.ht), vat = toCents(f.vat), ttc = toCents(f.ttc);
    if (ht !== null) body.ht_cents = ht; if (vat !== null) body.vat_cents = vat; if (ttc !== null) body.ttc_cents = ttc;
    if (edit && ht === null && vat === null && ttc === null) { /* montants inchangés */ }
    if (f.vat_recovery !== "") body.vat_recovery = Number(f.vat_recovery);
    if (f.deductible !== "") body.deductible = Number(f.deductible);
    if (f.reason) body.reason = f.reason;
    const r = edit ? await run(() => put(`/api/expenses/${x.id}`, body), "Dépense mise à jour") : await run(() => post("/api/expenses", body), "Dépense enregistrée");
    (r.warnings || []).forEach((w) => toast(w, true));
    openExpense(r.id || x.id); refreshView();
  };
}
async function openExpense(id) {
  const x = await api(`/api/expenses/${id}`);
  openDrawer(`<h2>${esc(x.supplier_name || "")} · ${esc(x.label)}</h2>
    <div style="margin-bottom:10px">${badge(x.status)} <span class="badge">${esc(x.source)}</span></div>
    ${x.warnings.length ? `<div class="alert warn"><ul style="margin:0 0 0 18px;padding:0">${x.warnings.map((w) => `<li>${esc(w)}</li>`).join("")}</ul></div>` : ""}
    <dl class="kv"><dt>Date</dt><dd>${d(x.date)}</dd><dt>Montants</dt><dd>HT ${eur(x.ht_cents)} · TVA ${eur(x.vat_cents)} (${esc(x.vat_rate || "")} %) · TTC <b>${eur(x.ttc_cents)}</b></dd>
      <dt>Régime de TVA</dt><dd>${REGIMES[x.vat_regime]} · récupérable ${x.vat_recovery} %</dd><dt>Catégorie</dt><dd>${esc(x.category || "Non catégorisée")}</dd>
      <dt>Traitement fiscal</dt><dd>${esc(x.rule_explanation || "")} Charge déductible ${x.deductible} %.</dd>
      <dt>Paiement</dt><dd>${PAYMENT[x.payment_method]} · payé ${eur(x.paid_cents)}</dd><dt>Justificatif</dt><dd>${x.document ? docLink(x.document)
        : x.doc_status === "perdu" ? `<span class="badge plain">Déclaré perdu</span> <span class="small muted">${esc(x.doc_note || "")}</span><div style="margin-top:6px">${attachButton("exp", x.id)} <button class="sm ghost" onclick="unlost(${x.id})">Annuler la déclaration</button></div>`
        : !x.needs_document ? `<span class="muted">Non requis${x.code === "banque" ? " (le relevé Qonto justifie ce frais sans TVA)" : ""}</span>`
        : `${attachButton("exp", x.id)} <button class="sm ghost" onclick="lostForm(${x.id})">J'ai perdu le justificatif</button>`}</dd>
      ${x.comment ? `<dt>Commentaire</dt><dd>${esc(x.comment)}</dd>` : ""}</dl>
    <div class="toolbar" style="margin-top:14px"><button onclick='expenseForm(${JSON.stringify(x).replace(/'/g, "&#39;")})'>Modifier</button>
      ${x.status !== "valide" ? `<button class="primary" onclick="validateExpense(${x.id})">Valider</button><button class="danger" onclick="deleteExpense(${x.id})">Supprimer</button>` : ""}</div>
    <h3>Écritures comptables</h3>${entriesHtml(x.entries)}
    <h3>Historique</h3>${x.history.map((h) => `<div class="small muted">${new Date(h.ts).toLocaleString("fr-FR")} · ${esc(h.action)} ${h.reason ? "· " + esc(h.reason) : ""}</div>`).join("")}`);
}
async function validateExpense(id) { await run(() => post(`/api/expenses/${id}/validate`), "Dépense validée"); openExpense(id); refreshView(); }
async function deleteExpense(id) { if (!confirm("Supprimer cette dépense ?")) return; await run(() => del(`/api/expenses/${id}`), "Dépense supprimée"); closeDrawer(); route(); }

// ------------------------------------------------------------------ associé
ROUTES.associe = async () => {
  setActions(`<button class="primary" onclick="movementForm('apport')">${ic("plus")}Enregistrer un apport</button><button onclick="movementForm('remboursement')">Enregistrer un remboursement</button>
    <button onclick="expenseForm(null,'perso')">Dépense payée personnellement</button>`);
  const a = await api("/api/associate");
  $("#view").innerHTML = `
    <div class="cards">
      <div class="card"><div class="label">Total injecté</div><div class="value">${eur(a.total_injecte_cents)}</div><div class="sub">${eur(a.apports_cents)} d'apports + ${eur(a.avances_frais_cents)} de frais avancés</div></div>
      <div class="card"><div class="label">Remboursé par la société</div><div class="value">${eur(a.remboursements_cents)}</div></div>
      <div class="card"><div class="label">Restant dû par la société</div><div class="value">${eur(a.du_par_la_societe_cents)}</div><div class="sub">Compte courant ${esc(a.sens.split(":")[0])}</div></div>
      <div class="card"><div class="label">Capital social</div><div class="value">${eur(a.capital_social_cents)}</div><div class="sub">Libéré comptabilisé ${eur(a.capital_libere_comptabilise_cents)}</div></div>
    </div>
    ${a.solde_cents < 0 ? `<div class="alert bad">${esc(a.sens)}</div>` : ""}
    <div class="alert info" style="margin-top:14px">Un apport en compte courant n'est ni un chiffre d'affaires ni une augmentation de capital : c'est une dette de la société envers vous (compte 455), remboursable à tout moment. Une augmentation de capital passe par une décision et des formalités spécifiques.</div>
    ${a.en_attente_de_virement.length ? `<h2>En attente de rapprochement avec Qonto</h2><div class="table-wrap"><table>${a.en_attente_de_virement.map((m) => `<tr><td>${d(m.date)}</td><td>${esc(m.type)}</td><td class="num">${eur(m.amount_cents)}</td><td>${esc(m.comment || "")}</td><td class="right"><button class="sm" onclick="linkMovement(${m.id}, ${m.type === "remboursement" ? -m.amount_cents : m.amount_cents}, '${m.date}')">Rapprocher</button> <button class="sm ghost" onclick="delMovement(${m.id})">Supprimer</button></td></tr>`).join("")}</table></div>` : ""}
    <h2>Mouvements du compte courant (455)</h2>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Opération</th><th class="right">Apport / avance</th><th class="right">Remboursement</th></tr></thead>
    <tbody>${a.mouvements.map((m) => `<tr><td>${d(m.date)}</td><td>${esc(m.line_label || m.label)} <span class="muted small">${esc(m.journal)}</span></td><td class="num">${m.credit_cents ? eur(m.credit_cents) : ""}</td><td class="num">${m.debit_cents ? eur(m.debit_cents) : ""}</td></tr>`).join("")}</tbody>
    <tfoot><tr><td colspan="2">Solde (positif : la société vous doit)</td><td colspan="2" class="num">${eur(a.solde_cents)}</td></tr></tfoot></table>
    ${a.mouvements.length ? "" : emptyState("Aucun mouvement.", "user")}</div>
    ${a.perso_expenses.length ? `<h2>Dépenses payées personnellement</h2><div class="table-wrap"><table>${a.perso_expenses.map((x) => `<tr class="click" tabindex="0" onclick="location.hash='#/depenses?id=${x.id}'"><td>${d(x.date)}</td><td>${esc(x.supplier_name || "")} ${esc(x.label)}</td><td>${esc(x.category || "")}</td><td class="num">${eur(x.ttc_cents)}</td></tr>`).join("")}</table></div>` : ""}`;
};
function movementForm(type) {
  openModal(`<h2 style="margin-top:0">${type === "apport" ? "Apport personnel" : "Remboursement de compte courant"}</h2><form id="mvf">
    <div class="grid2"><div class="field"><label>Date</label><input type="date" name="date" value="${today()}"></div><div class="field"><label>Montant</label><input name="amount" required></div></div>
    <div class="field"><label>Commentaire</label><input name="comment"></div>
    <p class="hint">Le virement Qonto correspondant sera recherché pour le rapprocher. Si le virement est déjà importé, vous pouvez aussi le catégoriser directement dans Banque.</p>
    <button class="primary" type="submit">Enregistrer</button></form>`);
  $("#mvf").onsubmit = async (e) => {
    e.preventDefault(); const f = formData(e.target);
    const r = await run(() => post("/api/associate/movements", { type, date: f.date, amount_cents: toCents(f.amount), comment: f.comment }), "Mouvement enregistré");
    closeModal();
    if (r.candidates && r.candidates.length) { linkMovement(r.id, null, null, r.candidates); } else route();
  };
}
async function linkMovement(id, amount, date, cands) {
  if (!cands) { const all = await api(`/api/transactions?status=a_verifier`); cands = all.filter((t) => t.amount_cents === amount); }
  openModal(`<h2 style="margin-top:0">Rapprocher d'un virement Qonto</h2>${cands.length ? cands.map((t) => `<div class="toolbar"><span>${d(t.date)} · ${esc(t.counterparty || t.label)} · ${eur(t.amount_cents)}</span><span class="spacer"></span><button class="sm primary" onclick="doLink(${id},${t.id})">Rapprocher</button></div>`).join("") : `<p class="muted">Aucun virement du même montant à rapprocher pour l'instant.</p>`}<button class="ghost" onclick="closeModal();route()">Plus tard</button>`);
}
async function doLink(mv, tx) { await run(() => post(`/api/associate/movements/${mv}/link`, { transaction_id: tx }), "Rapproché"); closeModal(); route(); }
async function delMovement(id) { if (!confirm("Supprimer ce mouvement ?")) return; await run(() => del(`/api/associate/movements/${id}`)); route(); }

// ------------------------------------------------------------------ TVA
ROUTES.tva = async (p) => {
  const year = Number(p.year || new Date().getFullYear());
  const periods = await api(`/api/vat/periods?year=${year}`);
  const cur = periods.find((x) => x.start === p.start) || periods.find((x) => x.start <= today() && today() <= x.end && !p.start) || periods.filter((x) => x.end < today()).pop() || periods[0];
  setActions(`<select style="width:auto" onchange="location.hash='#/tva?year='+this.value">${[year - 1, year, year + 1].map((y) => `<option ${y === year ? "selected" : ""}>${y}</option>`).join("")}</select>`);
  const [data, ex] = await Promise.all([api(`/api/vat/compute?start=${cur.start}&end=${cur.end}`), api(`/api/vat/exercise?date=${cur.start}`).catch(() => null)]);
  const L = data.lignes;
  const MOIS = (m) => new Date(m + "-15").toLocaleDateString("fr-FR", { month: "short", year: "2-digit" });
  const exHtml = ex ? `
    <h2 style="margin-top:0">Sur l'exercice <span class="muted small" style="font-weight:400">du ${d(ex.exercice.start_date)} au ${d(ex.exercice.end_date)}</span></h2>
    <div class="cards">
      <div class="card"><div class="label">TVA récupérable (déductible)</div><div class="value">${eur(ex.deductible_cents)}</div><div class="sub">Cumul de l'exercice</div></div>
      <div class="card"><div class="label">TVA collectée</div><div class="value">${eur(ex.collectee_cents)}</div><div class="sub">${ex.en_attente_cents ? `+ ${eur(ex.en_attente_cents)} en attente d'encaissement` : "Sur les encaissements"}</div></div>
      <div class="card"><div class="label">${ex.solde_cents < 0 ? "Crédit de TVA cumulé" : "TVA nette cumulée"}</div><div class="value">${eur(Math.abs(ex.solde_cents))}</div><div class="sub">Déjà payée : ${eur(ex.payee_cents)}</div></div>
      <div class="card"><div class="label">TVA non récupérable</div><div class="value">${eur(ex.non_recuperable_cents)}</div><div class="sub">Incluse dans les charges</div></div>
    </div>
    <details style="margin-top:10px"><summary class="small"><b>Détail mois par mois</b></summary>
    <div class="table-wrap" style="margin-top:8px"><table><thead><tr><th>Mois</th><th class="right">Collectée</th><th class="right">Récupérable</th><th class="right">Solde</th></tr></thead>
    <tbody>${ex.mois.map((m) => `<tr><td>${esc(MOIS(m.month))}</td><td class="num">${eur(m.collectee_cents)}</td><td class="num">${eur(m.deductible_cents)}</td><td class="num">${eurSigned(m.solde_cents)}</td></tr>`).join("")}
    <tr class="total"><td>Total</td><td class="num">${eur(ex.collectee_cents)}</td><td class="num">${eur(ex.deductible_cents)}</td><td class="num">${eurSigned(ex.solde_cents)}</td></tr></tbody></table></div></details>
    <h2>Période de déclaration</h2>` : "";
  const show = Object.values(L).filter((l) => (l.base_cents || l.taxe_cents) || ["16", "23", "28", "32"].includes(l.code));
  $("#view").innerHTML = exHtml + `
    <div class="toolbar">${periods.map((x) => `<button class="sm ${x.start === cur.start ? "primary" : ""}" onclick="location.hash='#/tva?year=${year}&start=${x.start}'">${esc(x.label)} ${x.status ? badge(x.status) : ""}</button>`).join("")}</div>
    <div class="cards">
      <div class="card"><div class="label">TVA collectée (brute)</div><div class="value">${eur(data.tva_brute_cents)}</div></div>
      <div class="card"><div class="label">TVA déductible</div><div class="value">${eur(data.tva_deductible_cents)}</div></div>
      <div class="card"><div class="label">${data.credit_cents ? "Crédit de TVA" : "TVA nette à payer"}</div><div class="value">${eur(data.credit_cents || data.a_payer_cents)}</div></div>
      <div class="card"><div class="label">TVA non récupérable</div><div class="value">${eur(data.non_recuperable_cents)}</div><div class="sub">Incluse dans les charges</div></div>
    </div>
    <p class="muted small" style="margin-top:12px">Période du ${d(cur.start)} au ${d(cur.end)} · formulaire ${cur.form}. La TVA sur prestations de services est retenue à l'encaissement (TVA collectée en attente : ${eur(data.tva_collectee_en_attente_cents)}). ${data.lignes_provisoires ? `<span class="badge b-warn">${data.lignes_provisoires} écriture(s) provisoire(s)</span>` : ""}</p>
    <div class="toolbar"><button class="primary" onclick="prepareVat('${cur.start}','${cur.end}')">Préparer ma déclaration</button>
      <a class="btn" href="/api/vat/export?start=${cur.start}&end=${cur.end}">${ic("download")}Exporter le dossier (PDF, Excel, justificatifs)</a></div>
    <h2>Rubriques de la déclaration ${cur.form}</h2>
    <div class="table-wrap"><table><thead><tr><th>Ligne</th><th>Libellé</th><th class="right">Base HT</th><th class="right">Taxe</th><th></th></tr></thead>
    <tbody>${show.map((l) => `<tr class="${l.detail.length ? "click" : ""}" ${l.detail.length ? `onclick="vatDetail('${l.code}')"` : ""}><td class="mono">${esc(l.code)}</td><td>${esc(l.libelle)}</td><td class="num">${l.base_cents !== null ? eur(l.base_cents) : ""}</td><td class="num"><b>${l.taxe_cents !== null ? eur(l.taxe_cents) : ""}</b></td><td class="small muted">${l.detail.length ? l.detail.length + " pièce(s)" : ""}</td></tr>`).join("")}</tbody></table></div>
    <p class="hint">Cliquez sur une ligne pour retrouver les factures qui la composent. Vérifiez la notice du formulaire de l'année avant report sur impots.gouv.fr. Aucune déclaration ni aucun paiement n'est transmis automatiquement.</p>`;
  window._vat = data;
};
function vatDetail(code) {
  const l = window._vat.lignes[code];
  openDrawer(`<h2>Ligne ${esc(code)} · ${esc(l.libelle)}</h2><div class="table-wrap"><table><thead><tr><th>Date</th><th>Pièce</th><th>Libellé</th><th class="right">Base</th><th class="right">Taxe</th></tr></thead>
    <tbody>${l.detail.map((x) => `<tr><td>${d(x.date)}</td><td class="mono">${esc(x.piece || "")}</td><td>${esc(x.libelle)} ${x.provisoire ? badge("brouillon") : ""} ${x.document_id ? `<a href="/api/documents/${x.document_id}/file" target="_blank">pièce</a>` : ""}</td><td class="num">${eur(x.base_cents)}</td><td class="num">${eur(x.taxe_cents)}</td></tr>`).join("")}</tbody></table></div>`);
}
async function prepareVat(start, end) {
  const r = await run(() => post("/api/vat/prepare", { start, end }));
  const ret = r.return; const blocking = r.issues.filter((i) => i.niveau === "bloquant").length;
  openDrawer(`<h2>Déclaration de TVA ${esc(r.form)}</h2>
    <p>Période du ${d(start)} au ${d(end)} · ${badge(ret.status)} ${r.locked ? "" : `<span class="badge ${blocking ? "b-warn" : "b-ok"}">${esc(r.statut || "")}</span>`}</p>
    <div class="cards"><div class="card"><div class="label">${r.data.credit_cents ? "Crédit de TVA" : "Total à payer (ligne 32)"}</div><div class="value">${eur(r.data.credit_cents || r.data.a_payer_cents)}</div></div></div>
    <h3>Contrôles avant déclaration</h3>${issuesHtml(r.issues)}
    <h3>Montants à reporter</h3><div class="table-wrap"><table>${Object.values(r.data.lignes).filter((l) => l.base_cents || l.taxe_cents).map((l) => `<tr><td class="mono">${esc(l.code)}</td><td>${esc(l.libelle)}</td><td class="num">${l.base_cents !== null ? eur(l.base_cents) : ""}</td><td class="num">${l.taxe_cents !== null ? eur(l.taxe_cents) : ""}</td></tr>`).join("")}</table></div>
    <div class="toolbar" style="margin-top:16px"><a class="btn" href="/api/vat/export?start=${start}&end=${end}">Télécharger le récapitulatif (ZIP)</a>
      ${ret.status === "preparee" ? `<button class="primary" onclick="validateVat(${ret.id}, ${blocking > 0})">Valider la déclaration</button>` : ""}
      ${ret.status === "validee" ? `<button class="accent" onclick="filedVat(${ret.id})">J'ai déposé la déclaration sur impots.gouv.fr</button>` : ""}</div>
    <p class="hint">La validation fige les écritures de la période et solde les comptes de TVA vers « TVA à décaisser » ou « Crédit de TVA à reporter ». Le paiement se rapproche ensuite dans Banque.</p>`);
}
async function validateVat(id, hasBlocking) {
  let force = false;
  if (hasBlocking) { if (!confirm("Des anomalies bloquantes subsistent. La déclaration restera signalée comme validée malgré les anomalies. Continuer ?")) return; force = true; }
  await run(() => post(`/api/vat/returns/${id}/validate${force ? "?force=true" : ""}`), "Déclaration validée"); closeDrawer(); route();
}
async function filedVat(id) { await run(() => post(`/api/vat/returns/${id}/filed`), "Déclaration marquée comme déposée"); closeDrawer(); route(); }

// ------------------------------------------------------------------ comptabilité
ROUTES.comptabilite = async (p) => {
  const tab = p.tab || "journal";
  const tabs = { journal: "Livre journal", balance: "Balance", grandlivre: "Grand livre", resultat: "Compte de résultat", bilan: "Bilan", immos: "Immobilisations", mois: "Vérifier le mois", saisie: "Écriture manuelle", historique: "Historique" };
  $("#view").innerHTML = `<div class="tabs">${Object.entries(tabs).map(([k, l]) => `<button class="${k === tab ? "active" : ""}" onclick="location.hash='#/comptabilite?tab=${k}'">${l}</button>`).join("")}</div><div id="tab"></div>`;
  const T = $("#tab");
  if (tab === "journal") {
    const start = p.start || "", end = p.end || "";
    const rows = await api(`/api/entries?start=${start}&end=${end}&journal=${p.journal || ""}`);
    T.innerHTML = `<div class="toolbar"><select style="width:auto" onchange="location.hash='#/comptabilite?tab=journal&journal='+this.value"><option value="">Tous les journaux</option>${["AC", "VT", "BQ", "OD", "AN"].map((j) => `<option ${p.journal === j ? "selected" : ""}>${j}</option>`).join("")}</select>
      <span class="muted small">${rows.length} écriture(s) · ${rows.filter((r) => r.status === "brouillon").length} provisoire(s)</span><span class="spacer"></span>
      <button onclick="validatePeriod()">Valider les écritures d'une période</button></div>${entriesHtml(rows.slice().reverse().slice(0, 300))}`;
  } else if (tab === "balance") {
    const b = await api("/api/reports/balance");
    T.innerHTML = `<div class="table-wrap"><table><thead><tr><th>Compte</th><th>Libellé</th><th class="right">Débit</th><th class="right">Crédit</th><th class="right">Solde débiteur</th><th class="right">Solde créditeur</th></tr></thead>
      <tbody>${b.rows.map((r) => `<tr class="click" tabindex="0" onclick="location.hash='#/comptabilite?tab=grandlivre&account=${r.account}'"><td class="mono">${r.account}</td><td>${esc(r.label || "")}</td><td class="num">${eur(r.debit)}</td><td class="num">${eur(r.credit)}</td><td class="num">${r.solde_debiteur ? eur(r.solde_debiteur) : ""}</td><td class="num">${r.solde_crediteur ? eur(r.solde_crediteur) : ""}</td></tr>`).join("")}</tbody>
      <tfoot><tr><td colspan="2">Total ${b.totals.equilibre ? badge("valide") : `<span class="badge b-bad">Déséquilibre</span>`}</td><td class="num">${eur(b.totals.debit)}</td><td class="num">${eur(b.totals.credit)}</td><td colspan="2"></td></tr></tfoot></table></div>`;
  } else if (tab === "grandlivre") {
    const g = await api(`/api/reports/grand-livre?account=${p.account || ""}`);
    T.innerHTML = `<div class="toolbar"><input placeholder="Compte (ex. 455, 6)" value="${esc(p.account || "")}" style="width:200px" onkeydown="if(event.key==='Enter')location.hash='#/comptabilite?tab=grandlivre&account='+this.value"></div>
      ${g.map((a) => `<h3>${a.account} · ${esc(a.label || "")}</h3><div class="table-wrap"><table><tbody>${a.lines.map((l) => `<tr><td>${d(l.date)}</td><td class="mono small">${esc(l.journal)} ${esc(l.number || "")}</td><td>${esc(l.label)}</td><td class="num">${l.debit_cents ? eur(l.debit_cents) : ""}</td><td class="num">${l.credit_cents ? eur(l.credit_cents) : ""}</td><td class="num muted">${eur(l.cumul)}</td></tr>`).join("")}</tbody>
      <tfoot><tr><td colspan="3">Solde</td><td class="num">${eur(a.debit)}</td><td class="num">${eur(a.credit)}</td><td class="num">${eur(a.solde)}</td></tr></tfoot></table></div>`).join("") || emptyState("Aucune écriture.", "book")}`;
  } else if (tab === "resultat") {
    const r = await api("/api/reports/resultat");
    T.innerHTML = statementHtml(r);
  } else if (tab === "bilan") {
    const b = await api("/api/reports/bilan");
    T.innerHTML = bilanHtml(b);
  } else if (tab === "immos") {
    const a = await api("/api/assets");
    T.innerHTML = `<div class="toolbar"><button class="primary" onclick='assetForm(${JSON.stringify(a.types)})'>Ajouter une immobilisation</button><span class="muted small">Les achats catégorisés « Immobilisations » sont ajoutés automatiquement. Amortissement linéaire au prorata temporis.</span></div>
      <div class="table-wrap"><table><thead><tr><th>Bien</th><th>Compte</th><th>Mise en service</th><th>Durée</th><th class="right">Valeur brute</th><th class="right">Dotation ${a.exercice.end_date.slice(0, 4)}</th><th class="right">Amortissements cumulés</th><th class="right">VNC</th><th></th></tr></thead>
      <tbody>${a.assets.map((x) => `<tr><td>${esc(x.label)} ${x.disposal_date ? `<span class="badge">Sorti le ${d(x.disposal_date)}</span>` : ""}</td><td class="mono">${x.account}</td><td>${d(x.in_service_date)}</td><td>${x.duration_years} ans</td><td class="num">${eur(x.cost_cents)}</td><td class="num">${eur(x.dotation_cents)}</td><td class="num">${eur(x.cumul_fin_cents)}</td><td class="num">${eur(x.vnc_fin_cents)}</td>
        <td>${x.disposal_date ? "" : `<button class="sm ghost" onclick="disposeAsset(${x.id})">Sortie</button>`}</td></tr>`).join("")}</tbody></table>${a.assets.length ? "" : emptyState("Aucune immobilisation.", "archive")}</div>`;
  } else if (tab === "mois") {
    const month = p.month || thisMonth();
    const r = await api(`/api/month/${month}/check`);
    T.innerHTML = `<div class="toolbar"><input type="month" value="${month}" style="width:auto" onchange="location.hash='#/comptabilite?tab=mois&month='+this.value">
      ${r.closed ? `<span class="badge b-ok">Mois clôturé le ${new Date(r.closed.closed_at).toLocaleDateString("fr-FR")}</span>` : ""}<span class="spacer"></span>
      <a class="btn" href="/api/month/${month}/export">Générer le dossier mensuel</a>
      <button class="primary" onclick="closeMonth('${month}', ${r.blocking})">Clôturer le mois</button></div>
      <p class="muted small">Contrôle des opérations bancaires, justificatifs, factures, écritures provisoires, doublons, rapprochement bancaire et TVA. Seuls les problèmes sont affichés. Une régularisation reste possible après clôture et sera tracée.</p>
      ${issuesHtml(r.issues, "Mois vérifié : aucun problème.")}`;
  } else if (tab === "saisie") {
    T.innerHTML = manualEntryHtml();
    accountsList().then((a) => { $("#acc-dl").innerHTML = a.map((x) => `<option value="${x.number}">${esc(x.label)}</option>`).join(""); });
  } else if (tab === "historique") {
    const h = await api("/api/audit");
    T.innerHTML = `<div class="table-wrap"><table><thead><tr><th>Date</th><th>Action</th><th>Objet</th><th>Motif</th></tr></thead><tbody>${h.map((x) => `<tr class="click" tabindex="0" onclick='showAudit(${JSON.stringify(x).replace(/'/g, "&#39;")})'><td class="small">${new Date(x.ts).toLocaleString("fr-FR")}</td><td>${esc(x.action)}</td><td>${esc(x.object_type)} ${x.object_id || ""}</td><td class="muted">${esc(x.reason || "")}</td></tr>`).join("")}</tbody></table></div>`;
  }
};
function showAudit(x) { openDrawer(`<h2>${esc(x.action)} · ${esc(x.object_type)} ${x.object_id || ""}</h2><p class="muted">${new Date(x.ts).toLocaleString("fr-FR")} ${esc(x.reason || "")}</p><h3>Avant</h3><pre class="mono small panel panel-pad" style="white-space:pre-wrap">${esc(JSON.stringify(JSON.parse(x.before || "null"), null, 2))}</pre><h3>Après</h3><pre class="mono small panel panel-pad" style="white-space:pre-wrap">${esc(JSON.stringify(JSON.parse(x.after || "null"), null, 2))}</pre>`); }
function statementHtml(r) {
  return `<div class="split">${Object.values(r.sections).filter((s) => s.lines.length).map((s) => `<div><h3>${esc(s.label)}</h3><div class="table-wrap"><table class="statement">${s.lines.map((l) => `<tr><td><span class="mono small muted">${l.account}</span> ${esc(l.label || "")}</td><td class="num">${eur(l.amount)}</td></tr>`).join("")}<tr class="total"><td>Total</td><td class="num">${eur(s.total)}</td></tr></table></div></div>`).join("")}</div>
    <h2>Synthèse</h2><div class="table-wrap"><table class="statement"><tr><td>Chiffre d'affaires</td><td class="num">${eur(r.chiffre_affaires)}</td></tr><tr><td>Résultat d'exploitation</td><td class="num">${eur(r.resultat_exploitation)}</td></tr><tr><td>Résultat financier</td><td class="num">${eur(r.resultat_financier)}</td></tr><tr><td>Résultat exceptionnel</td><td class="num">${eur(r.resultat_exceptionnel)}</td></tr><tr><td>Impôt sur les bénéfices</td><td class="num">${eur(r.impot)}</td></tr><tr class="total"><td>Résultat net</td><td class="num">${eur(r.resultat_net)}</td></tr></table></div>`;
}
function bilanHtml(b) {
  const side = (rows, total, title) => `<div><h3>${title}</h3><div class="table-wrap"><table class="statement">${rows.map((r) => `<tr><td>${esc(r.rubrique)}<div class="small muted">${r.accounts.map((a) => `${a.account} ${esc(a.label || "")} ${eur(a.amount)}`).join(" · ")}</div></td><td class="num">${eur(r.total)}</td></tr>`).join("")}<tr class="total"><td>Total</td><td class="num">${eur(total)}</td></tr></table></div></div>`;
  return `${b.equilibre ? "" : `<div class="alert bad">Bilan déséquilibré : vérifiez les écritures.</div>`}<div class="split">${side(b.actif, b.total_actif, "Actif")}${side(b.passif, b.total_passif, "Passif")}</div>`;
}
function manualEntryHtml() {
  setTimeout(() => { addEntryLine(); addEntryLine(); $("#mef").onsubmit = submitEntry; }, 0);
  return `<div class="alert info">Écritures d'inventaire ou corrections (charges constatées d'avance, factures non parvenues, produits à recevoir…). Les opérations courantes sont générées automatiquement depuis la banque, les factures et les dépenses.</div>
    <form id="mef" class="panel panel-pad"><datalist id="acc-dl"></datalist>
      <div class="grid3"><div class="field"><label>Date</label><input type="date" name="date" value="${today()}"></div><div class="field"><label>Libellé</label><input name="label" required></div><div class="field"><label>Pièce</label><input name="piece_ref"></div></div>
      <table class="lines-table"><thead><tr><th>Compte</th><th>Libellé</th><th>Débit</th><th>Crédit</th></tr></thead><tbody id="elines"></tbody></table>
      <div class="toolbar" style="margin-top:8px"><button type="button" class="sm" onclick="addEntryLine()">Ajouter une ligne</button><span id="etot" class="muted small"></span></div>
      <button class="primary" type="submit">Enregistrer (provisoire)</button></form>`;
}
function addEntryLine() { const tr = document.createElement("tr"); tr.innerHTML = `<td><input list="acc-dl" data-k="account"></td><td><input data-k="label"></td><td><input data-k="debit"></td><td><input data-k="credit"></td>`; $("#elines").appendChild(tr); tr.oninput = () => { let dd = 0, cc = 0; $$("#elines tr").forEach((r) => { dd += toCents($("[data-k=debit]", r).value) || 0; cc += toCents($("[data-k=credit]", r).value) || 0; }); $("#etot").innerHTML = `Débit ${eur(dd)} · Crédit ${eur(cc)} ${dd === cc ? badge("valide") : `<span class="badge b-bad">écart ${eur(dd - cc)}</span>`}`; }; }
async function submitEntry(e) {
  e.preventDefault(); const f = formData(e.target);
  const lines = $$("#elines tr").map((r) => ({ account: $("[data-k=account]", r).value, label: $("[data-k=label]", r).value, debit_cents: toCents($("[data-k=debit]", r).value) || 0, credit_cents: toCents($("[data-k=credit]", r).value) || 0 })).filter((l) => l.account);
  await run(() => post("/api/entries", { date: f.date, label: f.label, piece_ref: f.piece_ref, lines }), "Écriture enregistrée");
  location.hash = "#/comptabilite?tab=journal";
}
function validatePeriod() {
  openModal(`<h2 style="margin-top:0">Valider les écritures</h2><p class="muted">Les écritures validées sont numérotées et ne peuvent plus être modifiées : toute correction passera par une contrepassation tracée.</p><form id="vpf"><div class="grid2"><div class="field"><label>Du</label><input type="date" name="start"></div><div class="field"><label>Au</label><input type="date" name="end"></div></div><button class="primary">Valider</button></form>`);
  $("#vpf").onsubmit = async (e) => { e.preventDefault(); const r = await run(() => post("/api/entries/validate", formData(e.target))); toast(`${r.validated} écriture(s) validée(s)`); closeModal(); route(); };
}
async function closeMonth(month, blocking) {
  let force = false;
  if (blocking) { if (!confirm(`${blocking} anomalie(s) bloquante(s). Clôturer quand même ? (la clôture sera tracée comme forcée)`)) return; force = true; }
  await run(() => post(`/api/month/${month}/close${force ? "?force=true" : ""}`), "Mois clôturé"); route();
}
function assetForm(types) {
  openModal(`<h2 style="margin-top:0">Nouvelle immobilisation</h2><form id="af"><div class="field"><label>Bien</label><input name="label" required></div>
    <div class="grid2"><div class="field"><label>Type</label><select name="account">${Object.entries(types).map(([k, t]) => `<option value="${k}">${t.label} (${t.years} ans)</option>`).join("")}</select></div><div class="field"><label>Valeur HT</label><input name="cost" required></div></div>
    <div class="grid3"><div class="field"><label>Date d'achat</label><input type="date" name="acquisition_date" value="${today()}"></div><div class="field"><label>Mise en service</label><input type="date" name="in_service_date" value="${today()}"></div><div class="field"><label>Durée (ans)</label><input type="number" name="duration_years" placeholder="usuelle"></div></div>
    <p class="hint">Pour un achat payé par Qonto, catégorisez plutôt l'opération en « Immobilisations » : la fiche est créée automatiquement avec son écriture.</p><button class="primary">Enregistrer</button></form>`);
  $("#af").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target); await run(() => post("/api/assets", { ...f, cost_cents: toCents(f.cost), duration_years: f.duration_years || null }), "Immobilisation ajoutée"); closeModal(); route(); };
}
function disposeAsset(id) {
  openModal(`<h2 style="margin-top:0">Sortie d'immobilisation</h2><form id="df"><div class="grid2"><div class="field"><label>Date</label><input type="date" name="date" value="${today()}"></div><div class="field"><label>Prix de cession (0 si mise au rebut)</label><input name="price" value="0"></div></div><button class="primary">Enregistrer la sortie</button></form>`);
  $("#df").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target); const r = await run(() => post(`/api/assets/${id}/dispose`, { date: f.date, price_cents: toCents(f.price) })); toast(r.note); closeModal(); route(); };
}

// ------------------------------------------------------------------ clôture
ROUTES.cloture = async (p) => {
  const fys = await api("/api/fiscal-years");
  const fy = fys.find((f) => f.id == p.fy) || fys.find((f) => f.start_date <= today() && today() <= f.end_date) || fys[fys.length - 1];
  setActions(`<select style="width:auto" onchange="location.hash='#/cloture?fy='+this.value">${fys.map((f) => `<option value="${f.id}" ${f.id === fy.id ? "selected" : ""}>Exercice ${f.start_date.slice(0, 4)}${f.status === "closed" ? " (clôturé)" : ""}</option>`).join("")}</select>`);
  const est = await api(`/api/tax/estimate?fy_id=${fy.id}`);
  $("#view").innerHTML = `
    <div class="cards"><div class="card"><div class="label">Résultat comptable avant impôt</div><div class="value">${eur(est.resultat_comptable_cents)}</div></div>
      <div class="card"><div class="label">Résultat fiscal estimé</div><div class="value">${eur(est.resultat_fiscal_cents)}</div></div>
      <div class="card"><div class="label">Impôt sur les sociétés estimé</div><div class="value">${eur(est.is_estime_cents)}</div><div class="sub">${est.taux_reduit_eligible ? "Taux réduit de 15 % applicable" : "Taux normal : " + esc(est.taux_reduit_motifs_exclusion.join(", "))}</div></div>
      <div class="card"><div class="label">Solde d'IS estimé</div><div class="value">${eur(est.solde_estime_cents)}</div><div class="sub">Acomptes versés ${eur(est.acomptes_payes_cents)}</div></div></div>
    <details class="panel panel-pad" style="margin-top:12px"><summary>Détail du calcul de l'impôt</summary>
      <table class="statement" style="margin-top:8px"><tr><td>Résultat comptable</td><td class="num">${eur(est.resultat_comptable_cents)}</td></tr>
      ${est.reintegrations.map((r) => `<tr><td>+ ${esc(r.libelle)}</td><td class="num">${eur(r.montant_cents)}</td></tr>`).join("")}
      ${est.deficits_imputes_cents ? `<tr><td>− Déficits antérieurs imputés</td><td class="num">${eur(est.deficits_imputes_cents)}</td></tr>` : ""}
      <tr class="total"><td>Résultat fiscal</td><td class="num">${eur(est.resultat_fiscal_cents)}</td></tr>
      ${est.tranches.map((t) => `<tr><td>${esc(t.libelle)} sur ${eur(t.base_cents)}</td><td class="num">${eur(t.impot_cents)}</td></tr>`).join("")}
      <tr class="total"><td>IS estimé</td><td class="num">${eur(est.is_estime_cents)}</td></tr></table>
      <p class="hint">${est.remarques.map(esc).join("<br>")}<br>Échéance du solde : ${esc(est.echeance_solde)}</p></details>
    <div class="toolbar" style="margin-top:18px"><button class="primary" onclick="prepareYear(${fy.id})">Préparer mon bilan annuel</button>
      <a class="btn" href="/api/closing/${fy.id}/export">${ic("download")}Exporter mon dossier annuel</a><a class="btn" href="/api/closing/${fy.id}/fec">${ic("download")}Télécharger le FEC</a></div>
    <div id="yearprep"></div>
    <h2>Sauvegarde des données</h2>
    <div class="panel panel-pad"><p class="muted small" style="margin-top:0">Sauvegarde automatique quotidienne (30 dernières conservées, dans un dossier privé de votre hébergement). L'export complet contient toutes les données et tous les justificatifs ; les clés API n'y figurent jamais. Pensez aussi aux sauvegardes Ionos ou à UpdraftPlus.</p>
      <div class="toolbar"><a class="btn" href="/api/backup/export">Exporter toutes les données</a><button onclick="backupNow()">Sauvegarder maintenant</button>
      <button onclick="$('#restore').click()">Restaurer une archive</button><input type="file" id="restore" accept=".zip" hidden onchange="restoreArchive(this)"></div><div id="bklist" class="small muted"></div></div>
    <h2>Reprise d'un autre logiciel</h2>
    <div class="panel panel-pad"><p class="muted small" style="margin-top:0">Chargez le fichier de reprise (.json) préparé depuis Dougs : chaque opération Qonto reçoit la catégorie et la TVA de l'ancien outil, les trajets deviennent des frais kilométriques. Un contrôle à blanc est affiché avant toute écriture, et les opérations déjà traitées ne sont jamais modifiées.</p>
      <div class="toolbar"><button onclick="$('#reprise-input2').click()">${ic("upload")}Charger un fichier de reprise</button><input type="file" id="reprise-input2" accept=".json,application/json" hidden onchange="repriseFile(this)"></div></div>`;
  api("/api/backups").then((b) => { $("#bklist").innerHTML = b.slice(0, 5).map((x) => `${esc(x.name)} · ${Math.round(x.size / 1024)} ko`).join("<br>"); });
};
async function prepareYear(id) {
  toast("Préparation du bilan…");
  const r = await run(() => api(`/api/closing/prepare?fy_id=${id}`));
  const ob = r.obligations;
  $("#yearprep").innerHTML = `
    <h2>Préparation de la clôture ${badge(r.definitif ? "valide" : "a_verifier")} <span class="muted small">${esc(r.statut)}</span></h2>
    ${r.informations_manquantes.length ? `<div class="alert warn"><b>Informations à renseigner</b><ul style="margin:4px 0 0 18px;padding:0">${r.informations_manquantes.map((m) => `<li>${esc(m)}</li>`).join("")}</ul></div>` : ""}
    <h3>Contrôles de l'exercice</h3>${issuesHtml(r.anomalies, "Toutes les vérifications sont satisfaites.")}
    <h3>Questions d'inventaire</h3><div class="panel"><ul class="issues">${r.questions.map((q) => `<li><span style="flex:1">${esc(q.question)}<div class="hint">${esc(q.aide)}</div></span></li>`).join("")}</ul></div>
    <p class="hint">Écritures d'inventaire : <a href="#/comptabilite?tab=saisie">saisir une écriture</a>. Les amortissements et l'IS sont calculés automatiquement à chaque préparation.</p>
    <h3>Compte de résultat</h3>${statementHtml(r.compte_de_resultat)}
    <h3>Bilan</h3>${bilanHtml(r.bilan)}
    <h3>Documents</h3><div class="toolbar">${["bilan.pdf", "compte-de-resultat.pdf", "immobilisations-amortissements.pdf", "creances-dettes.pdf", "impot-societes.pdf", "elements-liasse-fiscale.pdf", "obligations-annuelles.pdf", "projet-decision-associe-unique.pdf"].map((n) => `<a class="btn sm" target="_blank" href="/api/closing/${id}/statements/${n.replace(".pdf", "")}">${n.replace(".pdf", "").replace(/-/g, " ")}</a>`).join("")}</div>
    <h3>Obligations applicables</h3><p class="muted small">Régime ${esc(ob.regime_is)} · liasse ${esc(ob.liasse)} · micro entreprise : ${ob.micro_entreprise ? "oui" : "non"}</p>
    <div class="table-wrap"><table>${ob.liste.map((o) => `<tr><td>${esc(o.obligation)}<div class="hint">${esc(o.note || "")}</div></td><td class="small">${esc(o.echeance)}</td></tr>`).join("")}</table></div>
    <h3>Affectation du résultat proposée</h3><p>Résultat ${eur(r.affectation_proposee.resultat_cents)} : réserve légale ${eur(r.affectation_proposee.reserve_legale_cents)}, report à nouveau ${eur(r.affectation_proposee.report_a_nouveau_cents)}. <span class="hint">${esc(r.affectation_proposee.note)}</span></p>
    <div class="toolbar">${r.exercice.status === "closed"
      ? `<button onclick="allocate(${id}, ${r.affectation_proposee.reserve_legale_cents})">Enregistrer l'affectation décidée</button>`
      : `<button class="accent" onclick="closeYear(${id}, ${!r.definitif})">Clôturer définitivement l'exercice</button>`}</div>`;
}
async function closeYear(id, provisional) {
  let force = false;
  if (provisional) { if (!confirm("Des contrôles ou informations restent à compléter. Clôturer quand même ? (clôture tracée comme forcée)")) return; force = true; }
  else if (!confirm("La clôture valide toutes les écritures de l'exercice et crée les à nouveaux. Elle est définitive. Continuer ?")) return;
  await run(() => post(`/api/closing/${id}/close${force ? "?force=true" : ""}`), "Exercice clôturé"); route();
}
async function allocate(id, reserve) { await run(() => post(`/api/closing/${id}/allocate`, { legal_reserve_cents: reserve }), "Affectation enregistrée"); }
async function backupNow() { const r = await run(() => post("/api/backups")); toast("Sauvegarde " + r.file); route(); }
async function restoreArchive(input) {
  if (!confirm("Restaurer cette archive ? Les données actuelles seront sauvegardées puis remplacées.")) return;
  const f = new FormData(); f.append("file", input.files[0]);
  await run(() => api("/api/backup/restore", { method: "POST", body: f }), "Données restaurées"); location.reload();
}

// ------------------------------------------------------------------ paramètres
ROUTES.parametres = async (p) => {
  const s = await api("/api/settings");
  const tab = p.tab || "societe";
  const tabs = { societe: "Société", telephone: "Téléphone", fiscal: "Fiscalité", qonto: "Qonto", ia: "IA", facturation: "Facturation", exercices: "Exercices", categories: "Catégories", comptes: "Plan comptable" };
  $("#view").innerHTML = `<div class="tabs">${Object.entries(tabs).map(([k, l]) => `<button class="${k === tab ? "active" : ""}" onclick="location.hash='#/parametres?tab=${k}'">${l}</button>`).join("")}</div><div id="tab" style="max-width:760px"></div>`;
  const T = $("#tab"); const c = s.company, t = s.tax, q = s.qonto, ai = s.ai;
  if (tab === "societe") {
    T.innerHTML = `${s.confirmed.company ? "" : `<div class="alert warn">Valeurs provisoires : vérifiez et enregistrez.</div>`}<form id="sf" class="panel panel-pad">
      <div class="grid2"><div class="field"><label>Dénomination</label><input name="name" value="${esc(c.name)}"></div><div class="field"><label>Forme</label><input name="legal_form" value="${esc(c.legal_form)}"></div></div>
      <div class="grid3"><div class="field"><label>SIREN</label><input name="siren" value="${esc(c.siren)}"></div><div class="field"><label>SIRET</label><input name="siret" value="${esc(c.siret)}"></div><div class="field"><label>N° TVA intracommunautaire</label><input name="vat_number" value="${esc(c.vat_number)}"></div></div>
      <div class="field"><label>Adresse</label><input name="address" value="${esc(c.address)}"></div>
      <div class="grid3"><div class="field"><label>Code NAF</label><input name="naf" value="${esc(c.naf)}"></div><div class="field"><label>Date de création</label><input type="date" name="creation_date" value="${esc(c.creation_date)}"></div><div class="field"><label>Capital social</label><input name="capital" value="${centsToInput(c.capital_cents)}"></div></div>
      <div class="grid2"><div class="field"><label>Associé unique (nom tel qu'il apparaît sur vos virements)</label><input name="associate_name" value="${esc(c.associate_name)}"></div>
        <div class="field"><label>Autres libellés de vos virements (séparés par des virgules)</label><input name="aliases" value="${esc((c.associate_aliases || []).join(", "))}"></div></div>
      <label class="check"><input type="checkbox" name="capital_fully_paid" ${c.capital_fully_paid ? "checked" : ""}> Capital entièrement libéré</label>
      <label class="check"><input type="checkbox" name="held_by_natural_persons_75" ${c.held_by_natural_persons_75 ? "checked" : ""}> Capital détenu à 75 % au moins par des personnes physiques</label>
      <div style="margin-top:14px"><button class="primary">Enregistrer</button></div></form>`;
    $("#sf").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
      const v = { ...c, ...f, capital_cents: toCents(f.capital), associate_aliases: f.aliases.split(",").map((x) => x.trim()).filter(Boolean) }; delete v.capital; delete v.aliases;
      await run(() => put("/api/settings/company", v), "Société enregistrée"); route(); };
  } else if (tab === "telephone") {
    T.innerHTML = `<div class="alert info">Aurea Compta est hébergée sur votre site WordPress : elle s'ouvre partout, sur ordinateur comme sur téléphone, avec votre compte administrateur.</div>
      <div class="panel panel-pad"><p style="margin-top:0">Adresse courte : <a class="mono" href="${esc(s.app_url)}">${esc(s.app_url)}</a></p><ol style="margin:0;padding-left:18px">
        <li>Sur le téléphone, ouvrez cette adresse et connectez vous avec votre compte WordPress.</li>
        <li><b>iPhone</b> : bouton Partager, puis « Sur l'écran d'accueil ». <b>Android</b> : menu ⋮, puis « Ajouter à l'écran d'accueil ».</li>
        <li>Pour photographier un ticket : Dépenses, puis « Déposer des justificatifs ».</li></ol>
        <p class="hint">Sécurité : activez la double authentification sur votre compte administrateur (par exemple l'extension gratuite « Two Factor ») et utilisez un mot de passe fort. Taille maximale d'un fichier envoyé sur votre hébergement : ${esc(s.upload_max)}.</p></div>`;
  } else if (tab === "fiscal") {
    T.innerHTML = `${s.confirmed.tax ? "" : `<div class="alert warn">Régimes non confirmés : les valeurs affichées sont provisoires. Renseignez votre situation réelle (voir votre attestation d'immatriculation ou votre espace professionnel impots.gouv.fr).</div>`}<form id="tf" class="panel panel-pad">
      <div class="grid2"><div class="field"><label>Régime de TVA</label><select name="vat_regime"><option value="reel_normal">Réel normal (CA3)</option><option value="reel_simplifie">Réel simplifié (CA12 annuelle + acomptes)</option><option value="franchise">Franchise en base</option></select></div>
        <div class="field"><label>Périodicité des déclarations</label><select name="vat_periodicity"><option value="mensuelle">Mensuelle</option><option value="trimestrielle">Trimestrielle (TVA annuelle &lt; 4 000 €)</option><option value="annuelle">Annuelle</option></select></div></div>
      <div class="grid2"><div class="field"><label>Date de début d'assujettissement à la TVA</label><input type="date" name="vat_start_date" value="${esc(t.vat_start_date)}"></div>
        <div class="field"><label>Activité principale</label><select name="main_activity"><option value="services">Prestations de services</option><option value="biens">Ventes de biens</option><option value="mixte">Mixte</option></select></div></div>
      <label class="check"><input type="checkbox" name="vat_on_debits" ${t.vat_on_debits ? "checked" : ""}> Option pour le paiement de la TVA d'après les débits (prestations de services)</label>
      <div class="grid2" style="margin-top:12px"><div class="field"><label>Régime d'imposition des bénéfices</label><select name="is_regime"><option value="reel_simplifie">IS · réel simplifié</option><option value="reel_normal">IS · réel normal</option></select></div>
        <div class="field"><label>Nombre de salariés</label><input type="number" name="salaried_count" value="${t.salaried_count || 0}"></div></div>
      <div class="grid2"><div class="field"><label>IS de l'exercice précédent (acomptes)</label><input name="previous_is" value="${centsToInput(t.previous_is_cents)}"></div>
        <div class="field"><label>Déficits fiscaux reportables</label><input name="loss" value="${centsToInput(t.loss_carryforward_cents)}"></div></div>
      <div style="margin-top:14px"><button class="primary">Enregistrer et confirmer</button></div></form>`;
    ["vat_regime", "vat_periodicity", "main_activity", "is_regime"].forEach((k) => { $(`[name=${k}]`).value = t[k]; });
    $("#tf").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
      const v = { ...t, ...f, salaried_count: Number(f.salaried_count || 0), previous_is_cents: toCents(f.previous_is) || 0, loss_carryforward_cents: toCents(f.loss) || 0 }; delete v.previous_is; delete v.loss;
      await run(() => put("/api/settings/tax", v), "Fiscalité enregistrée"); route(); };
  } else if (tab === "qonto") {
    T.innerHTML = `<div class="alert info">Connexion par l'API officielle Qonto (lecture seule). Créez une clé dans Qonto : Paramètres, Intégrations et partenariats, Clé API. L'identifiant et la clé sont chiffrés avec les clés secrètes de votre WordPress et ne sont jamais exportés.</div>
      <form id="qf" class="panel panel-pad"><div class="grid2"><div class="field"><label>Identifiant (login)</label><input name="qonto_login" placeholder="${esc(s.secrets.qonto_login || "")}"></div><div class="field"><label>Clé secrète</label><input type="password" name="qonto_secret_key" placeholder="${esc(s.secrets.qonto_secret_key || "")}"></div></div>
      <div class="grid3"><div class="field"><label>Environnement</label><select name="environment"><option value="production">Production</option><option value="sandbox">Sandbox (test)</option></select></div>
        <div class="field"><label>Synchronisation automatique (minutes)</label><input type="number" name="auto_sync_minutes" value="${q.auto_sync_minutes}"></div><div class="field"><label>Compte (vide = principal)</label><input name="bank_account_id" value="${esc(q.bank_account_id)}"></div></div>
      <label class="check"><input type="checkbox" name="enabled" ${q.enabled ? "checked" : ""}> Activer la synchronisation automatique (toutes les heures et à l'ouverture de l'application)</label>
      <div class="toolbar" style="margin-top:14px"><button class="primary">Enregistrer</button><button type="button" onclick="testQonto()">Tester la connexion</button><button type="button" onclick="syncQonto(true)">Importer tout l'historique</button></div>
      <div id="qres" class="small"></div></form>
      <p class="hint">Dernière synchronisation : ${q.last_sync ? new Date(q.last_sync).toLocaleString("fr-FR") : "jamais"}. Sans connexion API, importez l'export CSV de Qonto depuis la page Banque. Le serveur MCP officiel de Qonto peut par ailleurs être branché à un assistant IA externe ; l'application n'en a pas besoin.</p>`;
    $("[name=environment]").value = q.environment;
    $("#qf").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
      if (f.qonto_login || f.qonto_secret_key) await put("/api/secrets", { qonto_login: f.qonto_login || null, qonto_secret_key: f.qonto_secret_key || null });
      await run(() => put("/api/settings/qonto", { ...q, environment: f.environment, auto_sync_minutes: Number(f.auto_sync_minutes), bank_account_id: f.bank_account_id, enabled: f.enabled }), "Qonto enregistré"); route(); };
  } else if (tab === "ia") {
    T.innerHTML = `${s.ia_extension ? "" : `<div class="alert warn">Pour activer l'IA, installez aussi l'extension complémentaire « Aurea Compta IA » (fichier aurea-compta-ia.zip). Sans elle, l'application fonctionne entièrement : saisie manuelle et lecture des factures électroniques Factur-X.</div>`}<div class="alert info">L'IA (Claude, d'Anthropic) lit les justificatifs et répond aux questions. Elle n'est pas nécessaire au fonctionnement : tous les calculs sont faits par l'application. Les documents envoyés à l'IA transitent par l'API Anthropic. Les factures électroniques Factur-X sont lues sans IA.</div>
      <form id="iaf" class="panel panel-pad"><div class="field"><label>Clé API Anthropic</label><input type="password" name="key" placeholder="${esc(s.secrets.anthropic_api_key || "sk-ant-…")}"></div>
      <div class="grid2"><div class="field"><label>Modèle</label><input name="model" value="${esc(ai.model)}"></div><div class="field"><label>Seuil de rapprochement automatique</label><input name="th" value="${ai.auto_link_threshold}"></div></div>
      <label class="check"><input type="checkbox" name="enabled" ${ai.enabled ? "checked" : ""}> Activer la lecture automatique et l'assistant IA</label>
      <div style="margin-top:14px"><button class="primary">Enregistrer</button></div></form>`;
    $("#iaf").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
      if (f.key) await put("/api/secrets", { anthropic_api_key: f.key });
      await run(() => put("/api/settings/ai", { ...ai, model: f.model, auto_link_threshold: Number(f.th), enabled: f.enabled }), "IA enregistrée"); route(); };
  } else if (tab === "facturation") {
    const iv = s.invoicing;
    T.innerHTML = `<form id="ff" class="panel panel-pad"><div class="grid3"><div class="field"><label>Préfixe factures</label><input name="prefix" value="${esc(iv.prefix)}"></div><div class="field"><label>Préfixe avoirs</label><input name="credit_prefix" value="${esc(iv.credit_prefix)}"></div><div class="field"><label>Délai de paiement (jours)</label><input name="payment_terms_days" value="${iv.payment_terms_days}"></div></div>
      <div class="field"><label>Format</label><input name="pattern" value="${esc(iv.pattern)}"><div class="hint">Numérotation unique et continue par année, sans trou (CGI ann. II art. 242 nonies A).</div></div>
      <button class="primary">Enregistrer</button></form>
      <p class="hint">Facturation électronique : réception obligatoire depuis le 1er septembre 2026 via une plateforme agréée, émission obligatoire pour les PME et micro entreprises au 1er septembre 2027. Les factures Factur-X reçues sont déjà lues automatiquement ; l'émission via votre plateforme agréée pourra être branchée ultérieurement.</p>`;
    $("#ff").onsubmit = async (e) => { e.preventDefault(); await run(() => put("/api/settings/invoicing", { ...iv, ...formData(e.target) }), "Enregistré"); };
  } else if (tab === "exercices") {
    const fys = await api("/api/fiscal-years");
    T.innerHTML = `<div class="table-wrap"><table>${fys.map((f) => `<tr><td>Du ${d(f.start_date)} au ${d(f.end_date)}</td><td>${f.status === "closed" ? badge("valide") + " clôturé" : "ouvert"}</td><td>${f.status === "closed" ? "" : `<button class="sm ghost" onclick="editFy(${f.id},'${f.start_date}','${f.end_date}')">Modifier</button>`}</td></tr>`).join("")}</table></div>
      <div class="toolbar" style="margin-top:12px"><button onclick="editFy()">Ajouter un exercice</button></div><p class="hint">Le premier exercice d'une société peut durer jusqu'à 24 mois environ selon les statuts. Les dates figurent dans vos statuts.</p>`;
  } else if (tab === "categories") {
    const cats = await categories();
    T.innerHTML = `<div class="table-wrap"><table><thead><tr><th>Catégorie</th><th>Compte</th><th>TVA récup. %</th><th>Déductible %</th><th>Active</th><th></th></tr></thead><tbody>${cats.map((c) => `<tr data-id="${c.id}"><td><input data-k="name" value="${esc(c.name)}"></td><td><input data-k="account" value="${c.account}" style="width:90px"></td><td><input data-k="vat_recovery" value="${c.vat_recovery}" style="width:70px"></td><td><input data-k="deductible" value="${c.deductible}" style="width:70px"></td><td><input type="checkbox" data-k="active" ${c.active ? "checked" : ""}></td><td><button class="sm" onclick="saveCat(this)">OK</button></td></tr>`).join("")}</tbody></table></div>
      <div class="toolbar" style="margin-top:12px"><button onclick="newCat()">Nouvelle catégorie</button></div>`;
  } else if (tab === "comptes") {
    const a = await accountsList();
    T.innerHTML = `<div class="toolbar"><input id="na" placeholder="Numéro" style="width:120px"><input id="nl" placeholder="Libellé" style="width:300px"><button onclick="addAccount()">Ajouter</button></div><div class="table-wrap"><table>${a.map((x) => `<tr><td class="mono">${x.number}</td><td>${esc(x.label)}</td></tr>`).join("")}</table></div>`;
  }
};
async function testQonto() { const r = await run(() => post("/api/qonto/test")); $("#qres").innerHTML = `<div class="alert ok">Connecté à ${esc(r.name)} : ${r.accounts.map((a) => `${esc(a.name || a.iban)} (${eur(a.balance_cents)})${a.main ? " principal" : ""} · id ${esc(a.id)}`).join(", ")}</div>`; }
function editFy(id, s, e) {
  openModal(`<h2 style="margin-top:0">Exercice comptable</h2><form id="fyf"><div class="grid2"><div class="field"><label>Début</label><input type="date" name="start_date" value="${s || ""}"></div><div class="field"><label>Fin</label><input type="date" name="end_date" value="${e || ""}"></div></div><button class="primary">Enregistrer</button></form>`);
  $("#fyf").onsubmit = async (ev) => { ev.preventDefault(); await run(() => post("/api/fiscal-years", { ...formData(ev.target), replace_id: id || null }), "Exercice enregistré"); closeModal(); route(); };
}
async function saveCat(btn) { const tr = btn.closest("tr"); const v = { id: Number(tr.dataset.id) }; $$("[data-k]", tr).forEach((i) => { v[i.dataset.k] = i.type === "checkbox" ? (i.checked ? 1 : 0) : i.value; }); await run(() => post("/api/categories", v), "Catégorie enregistrée"); CACHE.categories = null; }
async function newCat() { const name = prompt("Nom de la catégorie"); if (!name) return; const account = prompt("Compte de charge (ex. 618100)", "618100"); await run(() => post("/api/categories", { name, account }), "Catégorie ajoutée"); CACHE.categories = null; route(); }
async function addAccount() { await run(() => post("/api/accounts", { number: $("#na").value, label: $("#nl").value }), "Compte ajouté"); CACHE.accounts = null; route(); }

// ------------------------------------------------------------------ assistant et recherche
function openAssistant() {
  openDrawer(`<h2>Assistant</h2><p class="muted small">Répond à partir de vos données réelles et explique ses calculs. Il ne modifie rien.</p>
    <div class="sugg">${["Combien ai-je dépensé ce mois-ci ?", "Combien ai-je injecté personnellement dans ma société ?", "Combien la société me doit-elle ?", "Quelle TVA dois-je déclarer ?", "Quelles factures sont manquantes ?", "Quel est mon résultat comptable actuel ?", "Mon compte courant d'associé est-il créditeur ou débiteur ?"].map((q) => `<button onclick="ask(this.textContent)">${esc(q)}</button>`).join("")}</div>
    <div class="chat" id="chat"></div><form id="askf" class="toolbar"><input name="q" placeholder="Votre question" autocomplete="off"><button class="primary">Envoyer</button></form>`);
  $("#askf").onsubmit = (e) => { e.preventDefault(); const q = e.target.q.value.trim(); if (q) { ask(q); e.target.q.value = ""; } };
}
async function ask(q) {
  const chat = $("#chat"); chat.insertAdjacentHTML("beforeend", `<div class="msg me">${esc(q)}</div><div class="msg bot muted" id="pending">…</div>`);
  try { const r = await post("/api/assistant", { question: q }); $("#pending").outerHTML = `<div class="msg bot">${esc(r.answer)}${r.mode === "local" ? `<div class="hint">Réponse calculée sans IA.</div>` : ""}</div>`; }
  catch (e) { $("#pending").outerHTML = `<div class="msg bot neg">${esc(e.message)}</div>`; }
}
function openSearch() {
  openModal(`<input id="sq" placeholder="Nom, date (2026-03), montant (120,00), fournisseur…" autofocus><div id="sr" style="margin-top:12px"></div>`);
  const input = $("#sq"); input.focus();
  let timer; input.oninput = () => { clearTimeout(timer); timer = setTimeout(async () => {
    if (input.value.length < 2) return; const r = await api(`/api/search?q=${encodeURIComponent(input.value)}`);
    const sec = (title, items, fn) => items.length ? `<h3>${title}</h3>${items.map(fn).join("")}` : "";
    $("#sr").innerHTML = sec("Opérations", r.transactions, (t) => `<div><a href="#/banque?tx=${t.id}" onclick="closeModal()">${d(t.date)} · ${esc(t.counterparty || t.label)} · ${eur(t.amount_cents)}</a></div>`) +
      sec("Dépenses", r.depenses, (x) => `<div><a href="#/depenses?id=${x.id}" onclick="closeModal()">${d(x.date)} · ${esc(x.supplier_name || "")} ${esc(x.label)} · ${eur(x.ttc_cents)}</a></div>`) +
      sec("Factures", r.factures, (x) => `<div><a href="#/recettes?id=${x.id}" onclick="closeModal()">${esc(x.number)} · ${esc(x.client)} · ${eur(x.ttc_cents)}</a></div>`) +
      sec("Pièces", r.pieces, (x) => `<div><a href="/api/documents/${x.id}/file" target="_blank">${esc(x.original_name)} ${x.amount_cents ? "· " + eur(x.amount_cents) : ""}</a></div>`) || `<p class="muted">Aucun résultat.</p>`;
  }, 200); };
}

themeIcon();
route();
// Synchronisation Qonto à l'ouverture si l'intervalle est écoulé (sans bloquer l'affichage).
post("/api/qonto/auto").then((r) => { if (r && r.result && r.result.imported) { toast(`${r.result.imported} nouvelle(s) opération(s) Qonto.`); refreshView(); } }).catch(() => {});
