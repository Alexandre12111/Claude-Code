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

async function api(path, opts = {}) {
  const o = { headers: {}, ...opts };
  if (o.body && !(o.body instanceof FormData)) { o.headers["Content-Type"] = "application/json"; o.body = JSON.stringify(o.body); }
  const r = await fetch(path, o);
  if (!r.ok) {
    let msg = r.statusText;
    try { const j = await r.json(); msg = typeof j.detail === "string" ? j.detail : JSON.stringify(j.detail); } catch (e) { /* ignore */ }
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
  t.textContent = msg;
  $("#toasts").appendChild(t);
  setTimeout(() => t.remove(), err ? 7000 : 3500);
}
async function run(fn, okMsg) {
  try { const r = await fn(); if (okMsg) toast(okMsg); return r; } catch (e) { toast(e.message, true); throw e; }
}
function openDrawer(html) { $("#drawer-body").innerHTML = html; $("#drawer").classList.add("open"); $("#drawer .drawer-panel").scrollTop = 0; }
function closeDrawer() { $("#drawer").classList.remove("open"); }
$("#drawer").addEventListener("click", (e) => { if (e.target.id === "drawer") closeDrawer(); });
function openModal(html) { $("#modal-body").innerHTML = html; $("#modal").classList.add("open"); }
function closeModal() { $("#modal").classList.remove("open"); }
$("#modal").addEventListener("click", (e) => { if (e.target.id === "modal") closeModal(); });
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") { closeDrawer(); closeModal(); }
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
function docLink(doc) { return doc ? `<a href="/api/documents/${doc.id}/file" target="_blank">${esc(doc.original_name || "Justificatif")}</a>` : `<span class="badge b-warn">Justificatif manquant</span>`; }

// ------------------------------------------------------------------ routeur
const ROUTES = {};
const TITLES = { "": "Vue d'ensemble", banque: "Banque et transactions", recettes: "Recettes et factures clients", depenses: "Dépenses et notes de frais",
  associe: "Apports personnels et compte courant", tva: "TVA", comptabilite: "Comptabilité et écritures", cloture: "Clôture annuelle et exports", parametres: "Paramètres" };
function params() { const q = location.hash.split("?")[1] || ""; return Object.fromEntries(new URLSearchParams(q)); }
async function route(keepOverlay = false) {
  if (keepOverlay !== true) { closeDrawer(); closeModal(); }
  const name = (location.hash.replace(/^#\/?/, "").split("?")[0]) || "";
  $$("#nav a").forEach((a) => a.classList.toggle("active", a.dataset.route === name));
  $("#page-title").textContent = TITLES[name] || "";
  $("#sidebar").classList.remove("open");
  setActions("");
  $("#view").innerHTML = `<div class="empty">Chargement…</div>`;
  try { await (ROUTES[name] || ROUTES[""])(params()); } catch (e) { $("#view").innerHTML = `<div class="alert bad">${esc(e.message)}</div>`; }
  refreshCounts();
}
window.addEventListener("hashchange", () => route());
async function refreshCounts() {
  try {
    const o = await api("/api/overview");
    $("#c-banque").textContent = o.a_verifier.operations || "";
    $("#c-depenses").textContent = (o.a_verifier.depenses + o.a_verifier.pieces_non_traitees) || "";
    const prov = o.parametres_provisoires;
    $("#banner").innerHTML = (o.mode_demo ? `<div class="alert info">Mode démonstration : données fictives séparées de votre base réelle.</div>` : "") +
      (prov.length ? `<div class="alert warn">Paramètres provisoires utilisés (${prov.map((p) => p === "tax" ? "régimes fiscaux" : "société").join(", ")}). <a href="#/parametres">Les confirmer</a></div>` : "");
  } catch (e) { /* ignore */ }
}

// ------------------------------------------------------------------ vue d'ensemble
ROUTES[""] = async () => {
  const o = await api("/api/overview");
  setActions(`<button onclick="syncQonto()">Synchroniser Qonto</button><button onclick="location.hash='#/comptabilite?tab=mois'">Vérifier le mois</button>`);
  const tva = o.tva || {};
  const tvaVal = tva.credit_cents ? eur(tva.credit_cents) : eur(tva.a_payer_cents || 0);
  const todo = o.a_verifier;
  const totalTodo = todo.operations + todo.depenses + todo.justificatifs_manquants + todo.pieces_non_traitees;
  $("#view").innerHTML = `
    <div class="cards">
      <div class="card link" onclick="location.hash='#/banque'"><div class="label">Solde bancaire</div><div class="value">${eur(o.solde_bancaire_cents)}</div><div class="sub">${esc(o.solde_source)}</div></div>
      <div class="card link" onclick="location.hash='#/recettes'"><div class="label">Recettes de l'exercice (HT)</div><div class="value">${eur(o.recettes_cents)}</div><div class="sub">Facturé du ${d(o.exercice.start_date)} au ${d(o.exercice.end_date)}</div></div>
      <div class="card link" onclick="location.hash='#/depenses'"><div class="label">Dépenses de l'exercice (HT)</div><div class="value">${eur(o.depenses_cents)}</div><div class="sub">Charges comptables, amortissements compris</div></div>
      <div class="card link" onclick="location.hash='#/cloture'"><div class="label">Résultat provisoire</div><div class="value ${o.resultat_cents < 0 ? "neg" : ""}">${eur(o.resultat_cents)}</div><div class="sub">${o.resultat_cents >= 0 ? "Bénéficiaire" : "Déficitaire"} avant impôt</div></div>
      <div class="card link" onclick="location.hash='#/tva'"><div class="label">${tva.credit_cents ? "Crédit de TVA estimé" : "TVA estimée à payer"}</div><div class="value">${tvaVal}</div><div class="sub">Période ${esc(tva.periode || "")}</div></div>
      <div class="card link" onclick="location.hash='#/associe'"><div class="label">Apports non remboursés</div><div class="value">${eur(o.apports_non_rembourses_cents)}</div><div class="sub">Somme due par la société</div></div>
      <div class="card link" onclick="location.hash='#/banque?status=a_verifier'"><div class="label">Opérations à vérifier</div><div class="value">${totalTodo}</div><div class="sub">${todo.operations} opération(s), ${todo.depenses} dépense(s), ${todo.justificatifs_manquants} justificatif(s) manquant(s)</div></div>
    </div>
    <h2>À faire</h2>
    <div class="panel"><ul class="issues">
      ${todo.operations ? `<li>${badge("a_verifier")}<span style="flex:1">${todo.operations} opération(s) bancaire(s) à catégoriser</span><a href="#/banque?status=a_verifier">Ouvrir</a></li>` : ""}
      ${todo.pieces_non_traitees ? `<li>${badge("a_verifier")}<span style="flex:1">${todo.pieces_non_traitees} justificatif(s) déposé(s) non rattaché(s)</span><a href="#/depenses?tab=pieces">Ouvrir</a></li>` : ""}
      ${todo.depenses ? `<li>${badge("a_verifier")}<span style="flex:1">${todo.depenses} dépense(s) à valider</span><a href="#/depenses?status=a_valider">Ouvrir</a></li>` : ""}
      ${todo.justificatifs_manquants ? `<li>${badge("info")}<span style="flex:1">${todo.justificatifs_manquants} dépense(s) sans justificatif</span><a href="#/depenses">Ouvrir</a></li>` : ""}
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
  route();
}

// ------------------------------------------------------------------ banque
ROUTES.banque = async (p) => {
  setActions(`<button class="primary" onclick="syncQonto()">Synchroniser Qonto</button>
    <button onclick="$('#csv-input').click()">Importer un CSV</button><input type="file" id="csv-input" accept=".csv" hidden onchange="importCsv(this)">
    <button onclick="manualTx()">Saisir une opération</button>`);
  const status = p.status || "";
  const month = p.month || "";
  const rows = await api(`/api/transactions?status=${status}&month=${month}&q=${encodeURIComponent(p.q || "")}`);
  const proposals = rows.filter((r) => r.status === "propose" && r.suggestion && r.suggestion.confidence >= 0.9).length;
  $("#view").innerHTML = `
    <div class="toolbar">
      <div class="tabs" style="margin:0;border:0">
        <button class="${status === "a_verifier" ? "active" : ""}" onclick="location.hash='#/banque?status=a_verifier'">À vérifier</button>
        <button class="${!status ? "active" : ""}" onclick="location.hash='#/banque'">Toutes</button>
        <button class="${status === "rapproche" ? "active" : ""}" onclick="location.hash='#/banque?status=rapproche'">Rapprochées</button>
      </div>
      <span class="spacer"></span>
      <input type="month" value="${esc(month)}" style="width:auto" onchange="location.hash='#/banque?status=${status}&month='+this.value">
      <input placeholder="Rechercher libellé ou montant" style="width:220px" value="${esc(p.q || "")}" onkeydown="if(event.key==='Enter')location.hash='#/banque?status=${status}&q='+encodeURIComponent(this.value)">
      ${proposals ? `<button class="accent" onclick="acceptAll()">Accepter ${proposals} proposition(s) sûre(s)</button>` : ""}
    </div>
    <div class="table-wrap"><table>
      <thead><tr><th>Date</th><th>Tiers</th><th>Libellé</th><th class="right">Montant</th><th>Statut</th><th>Affectation</th></tr></thead>
      <tbody>${rows.map((t) => `<tr class="click" onclick="openTx(${t.id})">
        <td>${d(t.date)}</td><td>${esc(t.counterparty || "")}</td><td class="muted">${esc(t.label || "")}${t.note ? `<div class="small">${esc(t.note)}</div>` : ""}</td>
        <td>${eurSigned(t.amount_cents)}</td><td>${badge(t.status)}</td>
        <td class="small">${t.allocations.length ? t.allocations.map((a) => esc(allocLabel(a))).join(", ") : (t.suggestion ? `<span class="muted">${esc(t.suggestion.explanation)}</span>` : "")}</td></tr>`).join("")}</tbody>
    </table>${rows.length ? "" : `<div class="empty">Aucune opération. Synchronisez Qonto ou importez un relevé CSV.</div>`}</div>`;
  if (p.tx) openTx(Number(p.tx));
};
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
      <dt>Justificatif</dt><dd>${t.documents.length ? t.documents.map(docLink).join(", ") : docLink(null)}</dd></dl>
    ${s && t.status !== "rapproche" ? `<div class="alert ${s.confidence >= 0.9 ? "info" : "warn"}"><b>Proposition</b> (confiance ${Math.round(s.confidence * 100)} %) : ${esc(s.explanation)}
      <div style="margin-top:8px"><button class="primary sm" onclick="acceptTx(${t.id})">Accepter</button></div></div>` : ""}
    ${t.allocations.length ? `<h3>Affectation actuelle</h3><div class="panel panel-pad">${t.allocations.map((a) => `<div>${esc(allocLabel(a))} : ${eur(a.amount_cents)}
      ${a.target && a.target_type === "depense" ? ` · <a href="#/depenses?id=${a.target.id}">${esc(a.target.supplier_name || "")} ${esc(a.target.label)}</a>` : ""}
      ${a.target && a.target_type === "vente" ? ` · <a href="#/recettes?id=${a.target.id}">Facture ${esc(a.target.number)}</a>` : ""}</div>`).join("")}</div>` : ""}
    <h3>${t.allocations.length ? "Modifier l'affectation" : "Catégoriser"}</h3>
    <form id="classify" class="panel panel-pad">
      <div class="field"><label>Nature de l'opération</label><select name="type" id="ctype">
        ${credit ? `<option value="vente">Règlement d'une facture client</option><option value="associe">Apport personnel en compte courant</option>
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
    if (v === "depense_nouvelle") h = `
      <div class="grid2"><div class="field"><label>Catégorie</label><select name="category_id">${cats.filter((c) => c.active).map((c) => `<option value="${c.id}" ${s && s.category_id == c.id ? "selected" : ""}>${esc(c.name)}</option>`).join("")}</select></div>
      <div class="field"><label>Fournisseur</label><input name="supplier_name" value="${esc(t.counterparty || "")}"></div></div>
      <div class="field"><label>Libellé</label><input name="label" value="${esc(t.label || "")}"></div>
      <div class="grid3"><div class="field"><label>TVA</label><select name="vat_regime">${Object.entries(REGIMES).map(([k, l]) => `<option value="${k}" ${s && s.vat_regime === k ? "selected" : ""}>${l}</option>`).join("")}</select></div>
      <div class="field"><label>Taux</label><select name="vat_rate">${["20", "10", "5.5", "2.1", "0"].map((r) => `<option ${t.qonto_vat_rate === r ? "selected" : ""}>${r}</option>`).join("")}</select></div>
      <div class="field"><label>Montant TVA (facultatif)</label><input name="vat" value="${centsToInput(t.qonto_vat_cents)}" placeholder="calculé"></div></div>
      <div class="grid2"><div class="field"><label>Nature</label><select name="nature"><option value="services">Services</option><option value="biens">Biens</option></select></div>
      <div class="field"><label>Justificatif (PDF, image)</label><input type="file" name="file" accept=".pdf,image/*"></div></div>
      <div class="field"><label>Commentaire (objet, invités, bénéficiaire…)</label><input name="comment"></div>`;
    else if (v === "depense") h = `<div class="field"><label>Facture fournisseur à payer</label><select name="expense_id">${exps.map((x) => `<option value="${x.id}">${d(x.date)} · ${esc(x.supplier_name || "")} · ${eur(x.ttc_cents)}</option>`).join("")}</select>
      ${exps.length ? "" : `<p class="hint">Aucune dépense « à payer ». Utilisez « Dépense (nouvelle) ».</p>`}</div>`;
    else if (v === "vente") { const open = invs.filter((i) => i.kind === "facture" && i.remaining_cents > 0);
      h = `<div class="field"><label>Facture client</label><select name="invoice_id">${open.map((i) => `<option value="${i.id}" ${s && s.invoice_id == i.id ? "selected" : ""}>${esc(i.number)} · ${esc(i.client)} · reste ${eur(i.remaining_cents)}</option>`).join("")}</select></div>
      <div class="field"><label>Montant affecté (si paiement partiel de plusieurs factures)</label><input name="amount" value="${centsToInput(t.amount_cents)}"></div>`; }
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
    if (f.type === "depense_nouvelle") {
      let doc = null;
      const file = e.target.querySelector("[name=file]").files[0];
      if (file) { const fd = new FormData(); fd.append("file", file); fd.append("process", "false"); doc = (await run(() => api("/api/documents", { method: "POST", body: fd }))).document_id; }
      Object.assign(payload, { category_id: Number(f.category_id), supplier_name: f.supplier_name, label: f.label, vat_regime: f.vat_regime,
        vat_rate: f.vat_rate, nature: f.nature, comment: f.comment, document_id: doc });
      if (f.vat) payload.vat_cents = toCents(f.vat);
    }
    if (f.type === "depense") payload.expense_id = Number(f.expense_id);
    if (f.type === "vente") payload.invoices = [{ invoice_id: Number(f.invoice_id), amount_cents: toCents(f.amount) }];
    if (f.type === "compte") payload.account = f.account;
    if (f.comment && f.type !== "depense_nouvelle") payload.comment = f.comment;
    await run(() => post(`/api/transactions/${id}/classify`, payload), "Opération enregistrée");
    openTx(id); refreshView();
  };
}
async function acceptTx(id) { await run(() => post(`/api/transactions/${id}/accept`), "Proposition acceptée"); openTx(id); refreshView(); }
function refreshView() { const y = window.scrollY; route(true).then(() => window.scrollTo(0, y)); }

// ------------------------------------------------------------------ recettes
ROUTES.recettes = async (p) => {
  setActions(`<button class="primary" onclick="invoiceForm()">Nouvelle facture</button>`);
  const rows = await api("/api/invoices");
  const tot = rows.filter((r) => r.kind === "facture").reduce((a, r) => a + r.remaining_cents, 0);
  $("#view").innerHTML = `
    <p class="muted">Une facture émise n'est pas un encaissement : elle devient « payée » lorsqu'un virement Qonto lui est rapproché. Reste à encaisser : <b>${eur(tot)}</b>.</p>
    <div class="table-wrap"><table><thead><tr><th>Numéro</th><th>Date</th><th>Client</th><th class="right">HT</th><th class="right">TVA</th><th class="right">TTC</th><th class="right">Reste dû</th><th>Statut</th></tr></thead>
    <tbody>${rows.map((r) => `<tr class="click" onclick="openInvoice(${r.id})"><td class="mono">${esc(r.number)}${r.kind === "avoir" ? ` <span class="badge b-info">Avoir</span>` : ""}</td><td>${d(r.issue_date)}</td><td>${esc(r.client)}</td>
      <td class="num">${eur(r.kind === "avoir" ? -r.ht_cents : r.ht_cents)}</td><td class="num">${eur(r.kind === "avoir" ? -r.vat_cents : r.vat_cents)}</td><td class="num">${eur(r.kind === "avoir" ? -r.ttc_cents : r.ttc_cents)}</td>
      <td class="num">${r.kind === "facture" ? eur(r.remaining_cents) : ""}</td><td>${badge(r.payment_status)}</td></tr>`).join("")}</tbody></table>
    ${rows.length ? "" : `<div class="empty">Aucune facture. Créez votre première facture ou importez un PDF.</div>`}</div>`;
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
      <div class="field"><label>PDF de la facture (facultatif)</label><input type="file" name="file" accept=".pdf"></div>
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
    <td><select data-k="rate">${["20", "10", "5.5", "2.1", "0"].map((r) => `<option>${r}</option>`).join("")}</select></td><td><button type="button" class="ghost sm" onclick="this.closest('tr').remove();invTotals()">✕</button></td>`;
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
ROUTES.depenses = async (p) => {
  setActions(`<button class="primary" onclick="$('#up').click()">Déposer des justificatifs</button><input type="file" id="up" multiple accept=".pdf,image/*" hidden onchange="uploadDocs(this.files)">
    <button onclick="expenseForm()">Nouvelle dépense</button><button onclick="expenseForm(null,'perso')">Note de frais payée personnellement</button>`);
  const tab = p.tab || "depenses";
  if (tab === "pieces") return renderPieces();
  const month = p.month || "";
  const rows = await api(`/api/expenses?month=${month}&status=${p.status || ""}`);
  const total = rows.filter((r) => r.rule !== "personnel").reduce((a, r) => ({ ht: a.ht + r.ht_cents, ttc: a.ttc + r.ttc_cents }), { ht: 0, ttc: 0 });
  $("#view").innerHTML = `
    <div id="dropzone" class="drop">Déposez ici vos factures et tickets (PDF, JPG, PNG) : lecture automatique et rapprochement avec Qonto.</div>
    <div class="toolbar" style="margin-top:14px">
      <div class="tabs" style="margin:0;border:0"><button class="active">Dépenses</button><button onclick="location.hash='#/depenses?tab=pieces'">Justificatifs déposés</button></div>
      <span class="spacer"></span>
      <select style="width:auto" onchange="location.hash='#/depenses?month=${month}&status='+this.value"><option value="">Tous statuts</option>${["a_valider", "propose_ia", "valide"].map((s) => `<option value="${s}" ${p.status === s ? "selected" : ""}>${STATUS[s][0]}</option>`).join("")}</select>
      <input type="month" style="width:auto" value="${esc(month)}" onchange="location.hash='#/depenses?month='+this.value">
    </div>
    <div class="table-wrap"><table><thead><tr><th>Date</th><th>Fournisseur</th><th>Libellé</th><th>Catégorie</th><th class="right">HT</th><th class="right">TVA</th><th class="right">TTC</th><th>Paiement</th><th>Pièce</th><th>Statut</th></tr></thead>
    <tbody>${rows.map((x) => `<tr class="click" onclick="openExpense(${x.id})"><td>${d(x.date)}</td><td>${esc(x.supplier_name || "")}</td><td class="muted">${esc(x.label)}</td><td>${esc(x.category || "–")}</td>
      <td class="num">${eur(x.ht_cents)}</td><td class="num">${eur(x.vat_cents)}${x.vat_recovery < 100 && x.vat_cents ? ` <span class="badge" title="TVA non récupérable">NR</span>` : ""}</td><td class="num">${eur(x.ttc_cents)}</td>
      <td class="small">${PAYMENT[x.payment_method]}</td><td>${x.document_id ? "✓" : `<span class="badge b-warn">Manquant</span>`}</td><td>${badge(x.status)}</td></tr>`).join("")}</tbody>
    <tfoot><tr><td colspan="4">Total (hors dépenses personnelles)</td><td class="num">${eur(total.ht)}</td><td></td><td class="num">${eur(total.ttc)}</td><td colspan="3"></td></tr></tfoot></table>
    ${rows.length ? "" : `<div class="empty">Aucune dépense sur cette période.</div>`}</div>`;
  setupDrop();
  if (p.id) openExpense(Number(p.id));
};
function setupDrop() {
  const z = $("#dropzone"); if (!z) return;
  z.onclick = () => $("#up").click();
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
  location.hash = "#/depenses?tab=pieces";
  route();
}
async function renderPieces() {
  const docs = await api("/api/documents?unlinked=true");
  $("#view").innerHTML = `
    <div id="dropzone" class="drop">Déposez ici vos factures et tickets (PDF, JPG, PNG).</div>
    <div class="toolbar" style="margin-top:14px"><div class="tabs" style="margin:0;border:0"><button onclick="location.hash='#/depenses'">Dépenses</button><button class="active">Justificatifs déposés</button></div></div>
    <p class="muted small">Justificatifs non encore rattachés. Les données lues par l'IA sont des propositions : vérifiez-les avant de valider.</p>
    <div class="table-wrap"><table><thead><tr><th>Fichier</th><th>Fournisseur</th><th>Date</th><th class="right">TTC</th><th>Lecture</th><th></th></tr></thead>
    <tbody>${docs.map((x) => `<tr><td><a href="/api/documents/${x.id}/file" target="_blank">${esc(x.original_name)}</a></td><td>${esc(x.party_name || "")}</td><td>${d(x.doc_date)}</td>
      <td class="num">${x.amount_cents ? eur(x.amount_cents) : ""}</td><td>${x.ai_data ? `<span class="badge ${x.ai_data.source === "facturx" ? "b-ok" : "b-ai"}">${x.ai_data.source === "facturx" ? "Factur-X" : "IA"}</span>` : `<span class="badge">${x.ai_status === "manuel" ? "Saisie manuelle" : "Non lu"}</span>`}</td>
      <td class="right"><button class="sm" onclick="openPiece(${x.id})">Traiter</button></td></tr>`).join("")}</tbody></table>
    ${docs.length ? "" : `<div class="empty">Aucun justificatif en attente.</div>`}</div>`;
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
        <div class="field"><label>Justificatif ${x.document_id ? "(remplacer)" : ""}</label><input type="file" name="file" accept=".pdf,image/*"></div></div>
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
      <dt>Paiement</dt><dd>${PAYMENT[x.payment_method]} · payé ${eur(x.paid_cents)}</dd><dt>Justificatif</dt><dd>${docLink(x.document)}</dd>
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
  setActions(`<button class="primary" onclick="movementForm('apport')">Enregistrer un apport</button><button onclick="movementForm('remboursement')">Enregistrer un remboursement</button>
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
    ${a.mouvements.length ? "" : `<div class="empty">Aucun mouvement.</div>`}</div>
    ${a.perso_expenses.length ? `<h2>Dépenses payées personnellement</h2><div class="table-wrap"><table>${a.perso_expenses.map((x) => `<tr class="click" onclick="location.hash='#/depenses?id=${x.id}'"><td>${d(x.date)}</td><td>${esc(x.supplier_name || "")} ${esc(x.label)}</td><td>${esc(x.category || "")}</td><td class="num">${eur(x.ttc_cents)}</td></tr>`).join("")}</table></div>` : ""}`;
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
  const data = await api(`/api/vat/compute?start=${cur.start}&end=${cur.end}`);
  const L = data.lignes;
  const show = Object.values(L).filter((l) => (l.base_cents || l.taxe_cents) || ["16", "23", "28", "32"].includes(l.code));
  $("#view").innerHTML = `
    <div class="toolbar">${periods.map((x) => `<button class="sm ${x.start === cur.start ? "primary" : ""}" onclick="location.hash='#/tva?year=${year}&start=${x.start}'">${esc(x.label)} ${x.status ? badge(x.status) : ""}</button>`).join("")}</div>
    <div class="cards">
      <div class="card"><div class="label">TVA collectée (brute)</div><div class="value">${eur(data.tva_brute_cents)}</div></div>
      <div class="card"><div class="label">TVA déductible</div><div class="value">${eur(data.tva_deductible_cents)}</div></div>
      <div class="card"><div class="label">${data.credit_cents ? "Crédit de TVA" : "TVA nette à payer"}</div><div class="value">${eur(data.credit_cents || data.a_payer_cents)}</div></div>
      <div class="card"><div class="label">TVA non récupérable</div><div class="value">${eur(data.non_recuperable_cents)}</div><div class="sub">Incluse dans les charges</div></div>
    </div>
    <p class="muted small" style="margin-top:12px">Période du ${d(cur.start)} au ${d(cur.end)} · formulaire ${cur.form}. La TVA sur prestations de services est retenue à l'encaissement (TVA collectée en attente : ${eur(data.tva_collectee_en_attente_cents)}). ${data.lignes_provisoires ? `<span class="badge b-warn">${data.lignes_provisoires} écriture(s) provisoire(s)</span>` : ""}</p>
    <div class="toolbar"><button class="primary" onclick="prepareVat('${cur.start}','${cur.end}')">Préparer ma déclaration</button>
      <a class="btn" href="/api/vat/export.zip?start=${cur.start}&end=${cur.end}">Exporter le dossier (PDF, Excel, justificatifs)</a></div>
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
    <div class="toolbar" style="margin-top:16px"><a class="btn" href="/api/vat/export.zip?start=${start}&end=${end}">Télécharger le récapitulatif (ZIP)</a>
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
      <tbody>${b.rows.map((r) => `<tr class="click" onclick="location.hash='#/comptabilite?tab=grandlivre&account=${r.account}'"><td class="mono">${r.account}</td><td>${esc(r.label || "")}</td><td class="num">${eur(r.debit)}</td><td class="num">${eur(r.credit)}</td><td class="num">${r.solde_debiteur ? eur(r.solde_debiteur) : ""}</td><td class="num">${r.solde_crediteur ? eur(r.solde_crediteur) : ""}</td></tr>`).join("")}</tbody>
      <tfoot><tr><td colspan="2">Total ${b.totals.equilibre ? badge("valide") : `<span class="badge b-bad">Déséquilibre</span>`}</td><td class="num">${eur(b.totals.debit)}</td><td class="num">${eur(b.totals.credit)}</td><td colspan="2"></td></tr></tfoot></table></div>`;
  } else if (tab === "grandlivre") {
    const g = await api(`/api/reports/grand-livre?account=${p.account || ""}`);
    T.innerHTML = `<div class="toolbar"><input placeholder="Compte (ex. 455, 6)" value="${esc(p.account || "")}" style="width:200px" onkeydown="if(event.key==='Enter')location.hash='#/comptabilite?tab=grandlivre&account='+this.value"></div>
      ${g.map((a) => `<h3>${a.account} · ${esc(a.label || "")}</h3><div class="table-wrap"><table><tbody>${a.lines.map((l) => `<tr><td>${d(l.date)}</td><td class="mono small">${esc(l.journal)} ${esc(l.number || "")}</td><td>${esc(l.label)}</td><td class="num">${l.debit_cents ? eur(l.debit_cents) : ""}</td><td class="num">${l.credit_cents ? eur(l.credit_cents) : ""}</td><td class="num muted">${eur(l.cumul)}</td></tr>`).join("")}</tbody>
      <tfoot><tr><td colspan="3">Solde</td><td class="num">${eur(a.debit)}</td><td class="num">${eur(a.credit)}</td><td class="num">${eur(a.solde)}</td></tr></tfoot></table></div>`).join("") || `<div class="empty">Aucune écriture.</div>`}`;
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
        <td>${x.disposal_date ? "" : `<button class="sm ghost" onclick="disposeAsset(${x.id})">Sortie</button>`}</td></tr>`).join("")}</tbody></table>${a.assets.length ? "" : `<div class="empty">Aucune immobilisation.</div>`}</div>`;
  } else if (tab === "mois") {
    const month = p.month || thisMonth();
    const r = await api(`/api/month/${month}/check`);
    T.innerHTML = `<div class="toolbar"><input type="month" value="${month}" style="width:auto" onchange="location.hash='#/comptabilite?tab=mois&month='+this.value">
      ${r.closed ? `<span class="badge b-ok">Mois clôturé le ${new Date(r.closed.closed_at).toLocaleDateString("fr-FR")}</span>` : ""}<span class="spacer"></span>
      <a class="btn" href="/api/month/${month}/export.zip">Générer le dossier mensuel</a>
      <button class="primary" onclick="closeMonth('${month}', ${r.blocking})">Clôturer le mois</button></div>
      <p class="muted small">Contrôle des opérations bancaires, justificatifs, factures, écritures provisoires, doublons, rapprochement bancaire et TVA. Seuls les problèmes sont affichés. Une régularisation reste possible après clôture et sera tracée.</p>
      ${issuesHtml(r.issues, "Mois vérifié : aucun problème.")}`;
  } else if (tab === "saisie") {
    T.innerHTML = manualEntryHtml();
    accountsList().then((a) => { $("#acc-dl").innerHTML = a.map((x) => `<option value="${x.number}">${esc(x.label)}</option>`).join(""); });
  } else if (tab === "historique") {
    const h = await api("/api/audit");
    T.innerHTML = `<div class="table-wrap"><table><thead><tr><th>Date</th><th>Action</th><th>Objet</th><th>Motif</th></tr></thead><tbody>${h.map((x) => `<tr class="click" onclick='showAudit(${JSON.stringify(x).replace(/'/g, "&#39;")})'><td class="small">${new Date(x.ts).toLocaleString("fr-FR")}</td><td>${esc(x.action)}</td><td>${esc(x.object_type)} ${x.object_id || ""}</td><td class="muted">${esc(x.reason || "")}</td></tr>`).join("")}</tbody></table></div>`;
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
      <a class="btn" href="/api/closing/${fy.id}/export.zip">Exporter mon dossier annuel</a><a class="btn" href="/api/closing/${fy.id}/fec">Télécharger le FEC</a></div>
    <div id="yearprep"></div>
    <h2>Sauvegarde des données</h2>
    <div class="panel panel-pad"><p class="muted small" style="margin-top:0">Sauvegarde automatique quotidienne de la base (30 dernières conservées). L'export complet contient la base et tous les justificatifs ; les clés API n'y figurent jamais.</p>
      <div class="toolbar"><a class="btn" href="/api/backup/export.zip">Exporter toutes les données</a><button onclick="backupNow()">Sauvegarder maintenant</button>
      <button onclick="$('#restore').click()">Restaurer une archive</button><input type="file" id="restore" accept=".zip" hidden onchange="restoreArchive(this)"></div><div id="bklist" class="small muted"></div></div>`;
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
    <h3>Documents</h3><div class="toolbar">${["bilan.pdf", "compte-de-resultat.pdf", "immobilisations-amortissements.pdf", "creances-dettes.pdf", "impot-societes.pdf", "elements-liasse-fiscale.pdf", "obligations-annuelles.pdf", "projet-decision-associe-unique.pdf"].map((n) => `<a class="btn sm" target="_blank" href="/api/closing/${id}/statements/${n}">${n.replace(".pdf", "").replace(/-/g, " ")}</a>`).join("")}</div>
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
    const r = await api("/api/remote");
    T.innerHTML = `<div class="alert info">Ouvrez Aurea Compta sur votre téléphone, avec tous vos documents, comme une application. L'ordinateur doit être allumé et l'application ouverte : vos données restent chez vous, sans hébergement.</div>
      ${!r.is_local ? `<div class="alert warn">Ce réglage se modifie depuis l'ordinateur.</div>` : `
      <form id="rf" class="panel panel-pad">
        <div class="field"><label>${r.password_set ? "Changer le mot de passe (facultatif)" : "Mot de passe du téléphone (8 caractères minimum)"}</label><input type="password" name="password" autocomplete="new-password"></div>
        <label class="check"><input type="checkbox" name="enabled" ${r.enabled ? "checked" : ""}> Autoriser l'accès depuis mon téléphone</label>
        <div style="margin-top:14px"><button class="primary">Enregistrer</button></div></form>`}
      ${r.enabled && !r.listening_network && r.is_local ? `<div class="alert warn">Redémarrage nécessaire pour ouvrir l'accès. <button class="sm" onclick="restartApp()">Redémarrer maintenant</button></div>` : ""}
      ${r.enabled && r.listening_network ? `
        <h2>Sur votre téléphone</h2>
        <div class="split"><div class="panel panel-pad" style="text-align:center">${r.qr ? `<div style="max-width:220px;margin:auto;background:#fff;padding:8px;border-radius:8px">${r.qr}</div><p class="small muted">Scannez avec l'appareil photo du téléphone</p>` : ""}
          ${r.urls.map((u) => `<div class="mono">${esc(u.url)} <span class="badge">${u.type === "tailscale" ? "partout (Tailscale)" : "même Wi-Fi"}</span></div>`).join("")}</div>
        <div class="panel panel-pad"><ol style="margin:0;padding-left:18px">
          <li>Connectez le téléphone au même Wi-Fi que l'ordinateur et ouvrez l'adresse ci-contre.</li>
          <li>Saisissez le mot de passe (une fois tous les 30 jours).</li>
          <li><b>iPhone</b> : bouton Partager, puis « Sur l'écran d'accueil ». <b>Android</b> : menu ⋮, puis « Ajouter à l'écran d'accueil ».</li>
          <li>Pour photographier un ticket : Dépenses, puis « Déposer des justificatifs ».</li></ol>
          <p class="hint">Hors de chez vous : installez l'application gratuite Tailscale sur l'ordinateur et sur le téléphone (même compte). Une adresse « partout » apparaîtra ici, chiffrée de bout en bout.</p></div></div>` : ""}`;
    if ($("#rf")) $("#rf").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
      const res = await run(() => put("/api/remote", { password: f.password || null, enabled: f.enabled }), "Réglage enregistré");
      if (res.restart_needed) await restartApp(); else route(); };
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
    T.innerHTML = `<div class="alert info">Connexion par l'API officielle Qonto (lecture seule). Créez une clé dans Qonto : Paramètres, Intégrations et partenariats, Clé API. L'identifiant et la clé sont stockés hors de la base, dans un fichier protégé, et ne sont jamais exportés.</div>
      <form id="qf" class="panel panel-pad"><div class="grid2"><div class="field"><label>Identifiant (login)</label><input name="qonto_login" placeholder="${esc(s.secrets.qonto_login || "")}"></div><div class="field"><label>Clé secrète</label><input type="password" name="qonto_secret_key" placeholder="${esc(s.secrets.qonto_secret_key || "")}"></div></div>
      <div class="grid3"><div class="field"><label>Environnement</label><select name="environment"><option value="production">Production</option><option value="sandbox">Sandbox (test)</option></select></div>
        <div class="field"><label>Synchronisation automatique (minutes)</label><input type="number" name="auto_sync_minutes" value="${q.auto_sync_minutes}"></div><div class="field"><label>Compte (vide = principal)</label><input name="bank_account_id" value="${esc(q.bank_account_id)}"></div></div>
      <label class="check"><input type="checkbox" name="enabled" ${q.enabled ? "checked" : ""}> Activer la synchronisation automatique lorsque l'application est ouverte</label>
      <div class="toolbar" style="margin-top:14px"><button class="primary">Enregistrer</button><button type="button" onclick="testQonto()">Tester la connexion</button><button type="button" onclick="syncQonto(true)">Importer tout l'historique</button></div>
      <div id="qres" class="small"></div></form>
      <p class="hint">Dernière synchronisation : ${q.last_sync ? new Date(q.last_sync).toLocaleString("fr-FR") : "jamais"}. Sans connexion API, importez l'export CSV de Qonto depuis la page Banque. Le serveur MCP officiel de Qonto peut par ailleurs être branché à un assistant IA externe ; l'application n'en a pas besoin.</p>`;
    $("[name=environment]").value = q.environment;
    $("#qf").onsubmit = async (e) => { e.preventDefault(); const f = formData(e.target);
      if (f.qonto_login || f.qonto_secret_key) await put("/api/secrets", { qonto_login: f.qonto_login || null, qonto_secret_key: f.qonto_secret_key || null });
      await run(() => put("/api/settings/qonto", { ...q, environment: f.environment, auto_sync_minutes: Number(f.auto_sync_minutes), bank_account_id: f.bank_account_id, enabled: f.enabled }), "Qonto enregistré"); route(); };
  } else if (tab === "ia") {
    T.innerHTML = `<div class="alert info">L'IA (Claude, d'Anthropic) lit les justificatifs et répond aux questions. Elle n'est pas nécessaire au fonctionnement : tous les calculs sont faits par l'application. Les documents envoyés à l'IA transitent par l'API Anthropic. Les factures électroniques Factur-X sont lues sans IA.</div>
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

async function restartApp() {
  await run(() => post("/api/restart"));
  toast("Redémarrage…");
  for (let i = 0; i < 30; i++) { await new Promise((r) => setTimeout(r, 700)); try { await api("/api/overview"); location.reload(); return; } catch (e) { /* encore en redémarrage */ } }
}
async function quitApp() {
  if (!confirm("Quitter Aurea Compta ? Vos données sont enregistrées. Relancez l'application d'un double clic.")) return;
  try { await post("/api/shutdown"); } catch (e) { toast(e.message, true); return; }
  document.body.innerHTML = `<div class="empty" style="margin-top:30vh">Aurea Compta est fermée. Vous pouvez fermer cet onglet.</div>`;
}
if (!["127.0.0.1", "localhost"].includes(location.hostname)) { const q = document.getElementById("quit-btn"); if (q) q.style.display = "none"; }

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

route();
