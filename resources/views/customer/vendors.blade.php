<x-app-layout page-title="Book Verified Experts | Professional Appointments">

{{-- ================================================================
     GLOBAL PAGE STYLES — inlined here so they take priority over
     any compiled Tailwind / app.css rules that were fighting us.
================================================================ --}}
<style>
/* ── Background ──────────────────────────────────────────────── */
.bv-page { background: linear-gradient(180deg,#0a0f2c 0%,#0d1333 100%); min-height:100vh; }

/* ── Hero ────────────────────────────────────────────────────── */
.bv-hero { position:relative; padding: 120px 24px 80px; overflow:hidden; }
.bv-hero-glow-1 { position:absolute; top:0; left:25%; width:500px; height:500px;
  background:rgba(255,109,0,.08); border-radius:50%; filter:blur(120px); pointer-events:none; }
.bv-hero-glow-2 { position:absolute; bottom:0; right:25%; width:600px; height:600px;
  background:rgba(255,109,0,.04); border-radius:50%; filter:blur(150px); pointer-events:none; }

/* ── Search Bar ──────────────────────────────────────────────── */
.bv-search-wrap { max-width:860px; margin:0 auto 40px; }
.bv-search-bar {
  display:flex; align-items:center;
  background:rgba(255,255,255,0.06);
  border:1px solid rgba(255,255,255,0.14);
  border-radius:16px;
  backdrop-filter:blur(22px);
  padding:8px;
  gap:0;
}
.bv-search-field {
  display:flex; align-items:center; gap:12px;
  flex:1; padding:12px 20px;
  border-right:1px solid rgba(255,255,255,0.1);
}
.bv-search-field:last-of-type { border-right:none; }
.bv-search-field svg { flex-shrink:0; color:rgba(255,140,66,.8); }
.bv-search-input {
  background:transparent; border:none; outline:none;
  color:#fff; font-size:15px; font-weight:600; width:100%;
  line-height:1.4;
}
.bv-search-input::placeholder { color:rgba(255,255,255,.38); font-weight:400; font-size:14px; }
.bv-search-input:focus { outline:none; }
.bv-search-caret {
  flex-shrink:0; opacity:.4;
}
.bv-search-btn {
  flex-shrink:0; margin-left:8px;
  background:linear-gradient(135deg,#ff6d00,#ffab40);
  color:#fff; font-weight:800; font-size:14px;
  border:none; border-radius:12px; padding:16px 32px;
  cursor:pointer; white-space:nowrap; letter-spacing:.04em;
  transition:filter .2s, transform .2s;
  box-shadow:0 6px 20px rgba(255,109,0,.4);
}
.bv-search-btn:hover { filter:brightness(1.1); transform:scale(1.02); }

/* ── Category Pills ──────────────────────────────────────────── */
.bv-cat-row {
  display:flex; align-items:center; justify-content:center;
  gap:10px; flex-wrap:wrap; margin-top:32px;
}
.bv-cat-pill {
  display:inline-flex; align-items:center; gap:10px;
  background:rgba(255,255,255,0.04);
  border:2px solid rgba(255,255,255,0.1);
  border-radius:999px; padding:7px 16px 7px 7px;
  text-decoration:none;
  transition:background .2s, border-color .2s, box-shadow .2s, transform .2s;
  cursor:pointer;
}
.bv-cat-pill:hover {
  background:rgba(var(--cr),var(--cg),var(--cb),0.15);
  border-color:rgba(var(--cr),var(--cg),var(--cb),0.5);
  transform:translateY(-2px);
  box-shadow:0 6px 18px rgba(var(--cr),var(--cg),var(--cb),0.25);
}
.bv-cat-pill.active {
  background:rgba(var(--cr),var(--cg),var(--cb),0.18);
  border-color:rgba(var(--cr),var(--cg),var(--cb),0.75);
  box-shadow:0 0 0 3px rgba(var(--cr),var(--cg),var(--cb),0.18), 0 6px 20px rgba(var(--cr),var(--cg),var(--cb),.3);
}
.bv-cat-icon {
  width:34px; height:34px; border-radius:50%;
  display:flex; align-items:center; justify-content:center;
  font-size:17px; flex-shrink:0;
  box-shadow:0 4px 12px rgba(0,0,0,.35);
}
.bv-cat-text { display:flex; flex-direction:column; }
.bv-cat-name { font-size:13px; font-weight:800; color:#fff; line-height:1; }
.bv-cat-sub  { font-size:10px; line-height:1.3; margin-top:2px; color:rgba(var(--cr),var(--cg),var(--cb),0.9); }

/* ── Stats ───────────────────────────────────────────────────── */
.bv-stats {
  display:flex; justify-content:center; gap:60px;
  margin-top:56px; padding-top:40px;
  border-top:1px solid rgba(255,255,255,.07); text-align:center;
}
.bv-stat-num {
  font-size:2.4rem; font-weight:900; color:#fff;
  display:flex; align-items:center; justify-content:center; gap:6px;
}
.bv-stat-label { font-size:11px; font-weight:700; text-transform:uppercase;
  letter-spacing:.18em; color:rgba(255,255,255,.3); margin-top:4px; }

/* ── Section header ──────────────────────────────────────────── */
.bv-section { padding:80px 24px; }
.bv-section-title { font-size:2.4rem; font-weight:900; color:#fff; text-align:center; margin-bottom:8px; }
.bv-section-accent { color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40);
  -webkit-background-clip:text; background-clip:text; font-style:italic; }
.bv-section-bar { width:72px; height:4px; background:linear-gradient(90deg,#ff6d00,#ffab40);
  border-radius:4px; margin:12px auto 8px; }
.bv-section-sub { font-size:10px; font-weight:700; text-transform:uppercase;
  letter-spacing:.35em; color:rgba(255,255,255,.25); text-align:center; }

/* ── Vendor Grid ─────────────────────────────────────────────── */
.bv-grid {
  display:grid; grid-template-columns:repeat(3,1fr); gap:22px;
  max-width:1100px; margin:48px auto 0;
}
@media(max-width:992px){ .bv-grid{ grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px){ .bv-grid{ grid-template-columns:1fr; } }

/* ── Vendor Card ─────────────────────────────────────────────── */
.bv-card {
  position:relative; border-radius:18px; overflow:hidden;
  background:rgba(14,18,40,0.85);
  border:1px solid rgba(255,255,255,0.1);
  display:flex; flex-direction:column;
  text-decoration:none; color:inherit;
  transition:transform .3s cubic-bezier(.16,1,.3,1), box-shadow .3s;
}
/* Gradient border glow on hover */
.bv-card::after {
  content:""; position:absolute; inset:0; border-radius:18px;
  padding:1px;
  background:linear-gradient(135deg,var(--c1,#2979ff),var(--c2,#00b0ff));
  -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
  -webkit-mask-composite:xor; mask-composite:exclude;
  opacity:0; transition:opacity .3s;
}
.bv-card:hover { transform:translateY(-6px); box-shadow:0 24px 60px rgba(0,0,0,.5); }
.bv-card:hover::after { opacity:1; }

.bv-card-img { position:relative; }
.bv-card-img img {
  width:100%; height:200px; object-fit:cover; display:block;
}
.bv-card-badge {
  position:absolute; top:12px; left:12px;
  background:linear-gradient(135deg,var(--c1,#2979ff),var(--c2,#00b0ff));
  color:#fff; font-size:9px; font-weight:900;
  text-transform:uppercase; letter-spacing:.07em;
  padding:4px 10px; border-radius:6px;
}
.bv-card-rating {
  position:absolute; top:12px; right:12px;
  background:rgba(0,0,0,0.75); backdrop-filter:blur(8px);
  border:1px solid rgba(255,255,255,.15);
  border-radius:999px; padding:4px 10px;
  display:flex; align-items:center; gap:5px;
  font-size:12px; font-weight:800; color:#fff;
}
.bv-card-body { padding:16px 16px 52px; flex:1; display:flex; flex-direction:column; }
.bv-card-name {
  font-size:17px; font-weight:800; color:#fff; margin:0 0 4px;
  display:flex; align-items:center; gap:6px;
}
.bv-card-verified { color:#38bdf8; flex-shrink:0; }
.bv-card-loc {
  display:flex; align-items:center; gap:5px;
  font-size:12px; color:rgba(255,255,255,.4); margin-bottom:16px;
  overflow:hidden;
}
.bv-card-loc span { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.bv-card-divider { height:1px; background:rgba(255,255,255,.07); margin-bottom:14px; }
.bv-card-price-label { font-size:9px; font-weight:700; text-transform:uppercase;
  letter-spacing:.07em; color:rgba(255,255,255,.35); margin-bottom:2px; }
.bv-card-price { font-size:20px; font-weight:900; color:#fff; }
.bv-card-cta {
  position:absolute; bottom:14px; right:14px;
  width:40px; height:40px; border-radius:12px;
  background:linear-gradient(135deg,#ff6d00,#ffab40);
  display:flex; align-items:center; justify-content:center;
  color:#fff; text-decoration:none; border:none;
  transition:transform .2s;
}
.bv-card-cta:hover { transform:scale(1.1); }

/* ── Category-Specific Cards ─────────────────────────────────── */
/* Global Card Base */
.bv-dynamic-card {
  position: relative; text-decoration: none; color: inherit;
  transition: transform .4s cubic-bezier(.16,1,.3,1), box-shadow .4s cubic-bezier(.16,1,.3,1), border-color .4s;
  display: flex; flex-direction: column;
  background: rgba(255,255,255,0.03);
  border-radius: 20px;
}

/* 1. Barber Card */
.bv-card-barber {
  height: 380px; 
  border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.25); 
  background: rgba(10, 15, 30, 0.7);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  overflow: hidden; 
  box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05), 0 8px 30px rgba(0,0,0,0.4);
}
.bv-card-barber:hover {
  transform: translateY(-8px) scale(1.02); 
  border-color: rgba(var(--cr), var(--cg), var(--cb), 0.6);
  box-shadow: inset 0 0 20px rgba(var(--cr), var(--cg), var(--cb), 0.15), 0 15px 40px rgba(var(--cr), var(--cg), var(--cb), 0.3);
}
.bv-card-barber-img-wrap {
  height: 80%; position: relative; overflow: hidden;
  border-radius: 19px 19px 0 0;
}
.bv-card-barber-img-wrap img {
  width: 100%; height: 100%; object-fit: cover; 
  transition: transform 0.6s cubic-bezier(.16,1,.3,1), filter 0.6s;
}
.bv-card-barber:hover .bv-card-barber-img-wrap img {
  transform: scale(1.08); filter: brightness(0.7);
}
.bv-card-barber-hover-overlay {
  position: absolute; inset: 0; 
  background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.4) 50%, transparent 100%);
  display: flex; flex-direction: column; justify-content: flex-end; padding: 24px;
}
/* Optional desc reveals on hover */
.bv-card-barber-desc {
  max-height: 0; opacity: 0; overflow: hidden; margin: 0;
  transition: max-height 0.3s ease, opacity 0.3s ease, margin 0.3s ease;
}
.bv-card-barber:hover .bv-card-barber-desc {
  max-height: 40px; opacity: 1; margin-top: 8px;
}

.bv-card-barber-price-bar {
  height: 20%; display: flex; align-items: center; justify-content: space-between;
  padding: 0 24px; 
  background: rgba(14, 18, 40, 0.85); 
  backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
  z-index: 2; border-top: 1px solid rgba(255,255,255,0.08);
}

/* 2. Consultant Card */
.bv-card-consultant {
  height: 380px; 
  border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.25); 
  background: rgba(14, 18, 40, 0.85);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  overflow: hidden; 
  box-shadow: 0 8px 30px rgba(0,0,0,0.4);
}
.bv-card-consultant:hover {
  transform: translateY(-8px) scale(1.02); 
  border-color: rgba(var(--cr), var(--cg), var(--cb), 0.6);
  box-shadow: 0 15px 40px rgba(var(--cr), var(--cg), var(--cb), 0.3);
}
.bv-card-consultant-img-wrap {
  height: 70%; position: relative; 
  padding: 20px 20px 10px;
}
.bv-card-consultant-img-wrap img {
  width: 100%; height: 100%; object-fit: cover; 
  border-radius: 24px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.5);
  transition: transform 0.6s cubic-bezier(.16,1,.3,1), filter 0.6s;
}
.bv-card-consultant:hover .bv-card-consultant-img-wrap img {
  transform: scale(1.05); filter: brightness(0.8);
}
.bv-card-consultant-overlay {
  position: absolute; inset: 20px 20px 10px; 
  border-radius: 24px; overflow: hidden;
  background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0) 60%);
  display: flex; flex-direction: column; justify-content: flex-end; padding: 20px;
}
.bv-card-consultant-price-bar {
  height: 30%; display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px; 
  background: rgba(14, 18, 40, 0.85); 
  border-top: 1px solid rgba(255,255,255,0.08);
}

/* 3. Sports Card */
.bv-card-sports {
  height: 380px; padding: 0; overflow: hidden; border: none;
  background: #000;
}
.bv-card-sports:hover {
  transform: translateY(-8px); box-shadow: 0 15px 40px rgba(var(--cr), var(--cg), var(--cb), 0.4);
}
.bv-card-sports img {
  position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover;
  transition: transform 0.6s cubic-bezier(.16,1,.3,1), opacity 0.4s;
  opacity: 0.8;
}
.bv-card-sports:hover img { transform: scale(1.08); opacity: 1; }
.bv-card-sports-overlay {
  position: absolute; inset: 0; 
  background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.4) 40%, rgba(0,0,0,0) 100%);
  display: flex; flex-direction: column; justify-content: flex-end; padding: 24px; z-index: 1;
}

/* 4. Doctor Card */
.bv-card-doctor {
  height: 380px; 
  background: linear-gradient(135deg, rgba(20, 30, 50, 0.9) 0%, rgba(10, 15, 30, 0.95) 100%);
  color: #f8fafc; 
  border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.3); 
  backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
  position: relative; overflow: hidden;
  box-shadow: 0 8px 30px rgba(0,0,0,0.4);
}
.bv-card-doctor:hover {
  transform: translateY(-6px); 
  border-color: var(--c1);
  box-shadow: 0 12px 30px rgba(var(--cr), var(--cg), var(--cb), 0.25);
}
.bv-card-doctor-img-wrap {
  height: 70%; position: relative; 
  padding: 20px 20px 10px;
}
.bv-card-doctor-img-wrap img {
  width: 100%; height: 100%; object-fit: cover; 
  border-radius: 24px;
  box-shadow: 0 8px 20px rgba(0,0,0,0.4);
  transition: transform 0.6s cubic-bezier(.16,1,.3,1), filter 0.6s;
}
.bv-card-doctor:hover .bv-card-doctor-img-wrap img {
  transform: scale(1.05); filter: brightness(0.85);
}
.bv-card-doctor-overlay {
  position: absolute; inset: 20px 20px 10px; 
  border-radius: 24px; overflow: hidden;
  background: linear-gradient(to top, rgba(10, 15, 30, 0.95) 0%, rgba(0,0,0,0) 60%);
  display: flex; flex-direction: column; justify-content: flex-end; padding: 20px;
}
.bv-card-doctor-price-bar {
  height: 30%; display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px; 
  border-top: 1px solid rgba(255,255,255,0.08);
}

/* 5. Training Card */
.bv-card-training {
  height: 380px; 
  border: 1px solid rgba(var(--cr), var(--cg), var(--cb), 0.25); 
  background: rgba(14, 18, 40, 0.85);
  backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px);
  overflow: hidden; 
  box-shadow: 0 8px 30px rgba(0,0,0,0.4);
}
.bv-card-training:hover {
  transform: translateY(-8px) scale(1.02); 
  border-color: rgba(var(--cr), var(--cg), var(--cb), 0.6);
  box-shadow: 0 15px 40px rgba(var(--cr), var(--cg), var(--cb), 0.3);
}
.bv-card-training-img-wrap {
  height: 70%; position: relative; 
  padding: 16px 16px 10px;
}
.bv-card-training-img-wrap img {
  width: 100%; height: 100%; object-fit: cover; 
  border-radius: 20px;
  box-shadow: 0 10px 20px rgba(0,0,0,0.5);
  transition: transform 0.6s cubic-bezier(.16,1,.3,1), filter 0.6s;
}
.bv-card-training:hover .bv-card-training-img-wrap img {
  transform: scale(1.05); filter: brightness(0.85);
}
.bv-card-training-overlay {
  position: absolute; inset: 16px 16px 10px; 
  border-radius: 20px; overflow: hidden;
  background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0) 60%);
  display: flex; flex-direction: column; justify-content: flex-end; padding: 20px;
}
.bv-card-training-price-bar {
  height: 30%; display: flex; align-items: center; justify-content: space-between;
  padding: 0 20px; 
  background: rgba(14, 18, 40, 0.85); 
  border-top: 1px solid rgba(255,255,255,0.08);
}

/* 6. General / Vendor */
.bv-card-general {
  padding: 24px; border: 1px solid rgba(255,255,255,0.1); 
  align-items: center; text-align: center; background: rgba(14,18,40,0.85);
}
.bv-card-general:hover {
  transform: translateY(-5px); border-color: var(--c1);
  box-shadow: 0 10px 30px rgba(var(--cr), var(--cg), var(--cb), 0.2);
}
.bv-card-general img {
  width: 60px; height: 60px; object-fit: cover; margin-bottom: 16px; border-radius: 10px;
}

/* ── Grayscale for closed vendors ────────────────────────────── */
.bv-closed { filter:grayscale(1); opacity:.55; }

/* ── Steps Section ───────────────────────────────────────────── */
.bv-steps-section {
  padding:80px 24px 100px; position:relative; overflow:hidden;
  background:linear-gradient(180deg,rgba(10,15,44,0.3) 0%,rgba(8,12,35,0.8) 100%);
}
.bv-steps-grid {
  display:grid; grid-template-columns:repeat(3,1fr); gap:20px;
  max-width:860px; margin:0 auto; position:relative;
}
/* dashed connector line centered on the icon area */
.bv-steps-grid::before {
  content:""; position:absolute; top:75px; left:calc(16.66% + 20px); right:calc(16.66% + 20px);
  height:2px; border-top:2px dashed rgba(255,255,255,.15); z-index:0;
}
@media(max-width:768px){ .bv-steps-grid{ grid-template-columns:1fr; } 
  .bv-steps-grid::before { display:none; }
}
.bv-step-card {
  background:rgba(255,255,255,0.04);
  border:1px solid rgba(255,255,255,0.1);
  border-radius:20px;
  padding:0 24px 28px;   /* no top padding — icon overflows above */
  text-align:center;
  position:relative; z-index:1;
  /* CRITICAL: allow icon to overflow above card edges */
  overflow:visible;
  margin-top:70px;       /* reserve space for the overflowing icon */
  transition:transform .3s cubic-bezier(.16,1,.3,1), box-shadow .3s;
}
.bv-step-card:hover { transform:translateY(-8px); box-shadow:0 24px 60px rgba(0,0,0,.5); border-color:rgba(255,109,0,.3); }
.bv-step-icon-wrap {
  width:140px; height:140px;
  /* Pull icon UP: half of height = 70px above card top edge */
  margin:-70px auto 20px;
  display:flex; align-items:center; justify-content:center;
  position:relative; z-index:2;
}
/* 3D floating image style with strong drop-shadow for floating look */
.bv-step-icon-wrap img {
  width:135px; height:135px; object-fit:contain;
  filter: drop-shadow(0 20px 32px rgba(0,0,0,0.65));
  transition:transform .4s cubic-bezier(.16,1,.3,1);
}
.bv-step-card:hover .bv-step-icon-wrap img { transform:scale(1.08) translateY(-8px); }
.bv-step-num {
  position:absolute; top:-6px; right:-6px;
  width:26px; height:26px; border-radius:50%;
  background:linear-gradient(135deg,#ff6d00,#ffab40);
  color:#fff; font-size:11px; font-weight:900;
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 4px 12px rgba(255,109,0,.5);
}
.bv-step-title {
  font-size:13px; font-weight:900; color:#fff;
  text-transform:uppercase; letter-spacing:.12em; margin:0 0 10px;
}
.bv-step-desc { font-size:12px; color:rgba(255,255,255,.35); line-height:1.7; margin:0; }

/* ── CTA section ─────────────────────────────────────────────── */
.bv-cta-section {
  padding:100px 24px; text-align:center;
  background:linear-gradient(180deg,#0d1333,#070c24);
  position:relative; overflow:hidden;
}
.bv-cta-glow {
  position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);
  width:800px; height:800px; border-radius:50%;
  background:rgba(255,109,0,.09); filter:blur(120px); pointer-events:none;
}
.bv-cta-badge {
  display:inline-flex; align-items:center; gap:8px;
  background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1);
  border-radius:999px; padding:6px 16px;
  font-size:10px; font-weight:800; color:rgba(255,255,255,.6);
  text-transform:uppercase; letter-spacing:.2em; margin-bottom:28px;
}
.bv-cta-title {
  font-size:clamp(2.5rem,6vw,5rem); font-weight:900; color:#fff;
  line-height:1.05; letter-spacing:-.02em; margin:0 0 20px;
}
.bv-cta-accent { color:transparent;
  background:linear-gradient(135deg,#ff6d00,#ffab40);
  -webkit-background-clip:text; background-clip:text; font-style:italic;
}
.bv-cta-desc { color:rgba(255,255,255,.4); font-size:17px;
  max-width:540px; margin:0 auto 40px; line-height:1.7; }
.bv-cta-btn {
  display:inline-flex; align-items:center; gap:10px;
  background:linear-gradient(135deg,#ff6d00,#ffab40);
  color:#fff; font-weight:800; font-size:14px;
  padding:16px 36px; border-radius:12px; border:none;
  cursor:pointer; text-decoration:none; letter-spacing:.05em;
  text-transform:uppercase;
  box-shadow:0 14px 40px rgba(255,109,0,.4);
  transition:filter .2s, transform .2s;
}
.bv-cta-btn:hover { filter:brightness(1.1); transform:translateY(-2px); }
</style>

<div class="bv-page">

{{-- ═══════════════════════════════════════════════════════
     HERO + SEARCH + CATEGORIES
═══════════════════════════════════════════════════════ --}}
<section class="bv-hero">
    <div class="bv-hero-glow-1"></div>
    <div class="bv-hero-glow-2"></div>

    <div style="position:relative; z-index:10; text-align:center;">

        {{-- Badge --}}
        <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:999px; padding:8px 20px; font-size:10px; font-weight:800; color:rgba(255,255,255,.7); text-transform:uppercase; letter-spacing:.25em; margin-bottom:32px;">
            <span style="width:8px; height:8px; border-radius:50%; background:#ff6d00; display:inline-block; box-shadow:0 0 10px rgba(255,109,0,.6);"></span>
            PREMIUM MULTI-VENDOR PLATFORM
        </div>

        {{-- H1 --}}
        <h1 style="font-size:clamp(2.8rem,7vw,5rem); font-weight:900; color:#fff; line-height:1.05; letter-spacing:-.03em; margin:0 0 18px;">
            Book Verified <span style="color:transparent; background:linear-gradient(135deg,#ff8c42,#ffab40); -webkit-background-clip:text; background-clip:text; font-style:italic;">Experts</span><br>
            In Your City
        </h1>

        {{-- Subheading --}}
        <p style="color:rgba(255,255,255,.45); font-size:1.1rem; max-width:520px; margin:0 auto 48px; line-height:1.7;">
            Personalized platform to find top-rated professionals near you
        </p>

        {{-- ── Search Bar ── --}}
        <div class="bv-search-wrap">
            <div class="bv-search-bar">
                <form action="{{ route('home') }}" method="GET" style="display:flex; width:100%; align-items:center;">
                    @if(request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif

                    {{-- Expert Name --}}
                    <div class="bv-search-field">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <input class="bv-search-input" type="text" name="search" value="{{ request('search') }}" placeholder="Expert Name">
                    </div>

                    {{-- Specialty --}}
                    <div class="bv-search-field">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <input class="bv-search-input" type="text" name="specialty" value="{{ request('specialty') }}" placeholder="Specialty">
                        <svg class="bv-search-caret" width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>

                    {{-- Location --}}
                    <div class="bv-search-field" style="border-right:none;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <input class="bv-search-input" type="text" name="location" value="{{ request('location') }}" placeholder="Location (City)">
                        <svg class="bv-search-caret" width="14" height="14" fill="none" stroke="white" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>

                    <button class="bv-search-btn" type="submit">Search</button>
                </form>
            </div>

            {{-- ── Category Pills ── --}}
            <div class="bv-cat-row">
                @php
                    $catMeta = [
                        'health'     => ['g'=>['#00c853','#64dd17'], 'rgb'=>'0,200,83',   'sub'=>'Green Care'],
                        'doctor'     => ['g'=>['#00c853','#64dd17'], 'rgb'=>'0,200,83',   'sub'=>'Health Care'],
                        'beauty'     => ['g'=>['#ff6d00','#ffab40'], 'rgb'=>'255,109,0',  'sub'=>'Best Stylists'],
                        'barber'     => ['g'=>['#ff6d00','#ffab40'], 'rgb'=>'255,109,0',  'sub'=>'Mens Grooming'],
                        'sports'     => ['g'=>['#ffd600','#ffea00'], 'rgb'=>'255,214,0',  'sub'=>'Active Routine'],
                        'activity'   => ['g'=>['#ffd600','#ffea00'], 'rgb'=>'255,214,0',  'sub'=>'Active Routine'],
                        'consultant' => ['g'=>['#2979ff','#00b0ff'], 'rgb'=>'41,121,255', 'sub'=>'Pro & Prime'],
                        'training'   => ['g'=>['#7c3aed','#a78bfa'], 'rgb'=>'124,58,237', 'sub'=>'Get Stronger'],
                        'default'    => ['g'=>['#1a237e','#3949ab'], 'rgb'=>'26,35,126',  'sub'=>'All Experts'],
                    ];
                    // Split the "All" pill RGB
                    $allRgb = '255,109,0';
                @endphp

                {{-- All Services --}}
                <a href="{{ request()->fullUrlWithQuery(['type'=>'']) }}"
                   class="bv-cat-pill {{ !request('type') ? 'active' : '' }}"
                   style="--cr:255;--cg:109;--cb:0;">
                    <div class="bv-cat-icon" style="background:linear-gradient(135deg,#ff6d00,#ffab40);">⭐</div>
                    <div class="bv-cat-text">
                        <span class="bv-cat-name">All</span>
                        <span class="bv-cat-sub" style="color:rgba(255,171,64,0.9);">Services</span>
                    </div>
                </a>

                @foreach($allThemes as $key => $t)
                    @php
                        $cm  = $catMeta[$key] ?? $catMeta['default'];
                        $g   = $cm['g'];
                        $rgb = $cm['rgb'];
                        // parse rgb to individual r,g,b for CSS vars
                        [$cr,$cg,$cb] = explode(',', $rgb);
                        $iconStyle = "background:linear-gradient(135deg,{$g[0]},{$g[1]});";
                    @endphp
                    <a href="{{ request()->fullUrlWithQuery(['type'=>$key]) }}"
                       class="bv-cat-pill {{ request('type')==$key ? 'active':'' }}"
                       style="--cr:{{ trim($cr) }};--cg:{{ trim($cg) }};--cb:{{ trim($cb) }};">
                        <div class="bv-cat-icon" style="{{ $iconStyle }}">{{ $t['emoji'] ?? '✨' }}</div>
                        <div class="bv-cat-text">
                            <span class="bv-cat-name">{{ $t['label'] }}</span>
                            <span class="bv-cat-sub">{{ $cm['sub'] }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- ── Stats ── --}}
        <div class="bv-stats">
            <div>
                <div class="bv-stat-num"><span data-counter data-target="80" data-suffix="K+">0</span></div>
                <div class="bv-stat-label">Happy Clients</div>
            </div>
            <div>
                <div class="bv-stat-num"><span data-counter data-target="500" data-suffix="+">0</span></div>
                <div class="bv-stat-label">Cities Reach</div>
            </div>
            <div>
                <div class="bv-stat-num"><span data-counter data-target="1.2" data-suffix="M" data-decimals="1">0</span></div>
                <div class="bv-stat-label">Appointments</div>
            </div>
            <div>
                <div class="bv-stat-num">
                    <span data-counter data-target="4.9" data-decimals="1">0</span>
                    <span style="color:#ffab40; font-size:1.6rem;">★</span>
                </div>
                <div class="bv-stat-label">User Rating</div>
            </div>
        </div>

    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     RECOMMENDED PROFESSIONALS
═══════════════════════════════════════════════════════ --}}
<section class="bv-section">
    <div style="max-width:1100px; margin:0 auto;">
        <div>
            <h2 class="bv-section-title">
                Recommended <span class="bv-section-accent">Professionals</span>
            </h2>
            <div class="bv-section-bar"></div>
            <p class="bv-section-sub">Handpicked specialists for your premium experience</p>
        </div>

        <div class="bv-grid">
            @forelse($vendors as $vendor)
                @php
                    $vType   = $vendor->category?->slug ?? 'consultant';
                    $vTheme  = $allThemes[$vType] ?? $allThemes['consultant'] ?? [];
                    $isOpen  = $vendor->is_currently_open;

                    $catColors = [
                        'health'     => ['#00c853','#64dd17'],
                        'doctor'     => ['#00c853','#64dd17'],
                        'beauty'     => ['#ff6d00','#ffab40'],
                        'barber'     => ['#ff6d00','#ffab40'],
                        'sports'     => ['#ffd600','#ffea00'],
                        'activity'   => ['#ffd600','#ffea00'],
                        'consultant' => ['#2979ff','#00b0ff'],
                        'training'   => ['#7c3aed','#a78bfa'],
                        'default'    => ['#1a237e','#3949ab'],
                    ];
                    [$c1,$c2] = $catColors[$vType] ?? $catColors['default'];
                    
                    // RGB variables for shadow
                    $rgbStr = match($vType) {
                        'health','doctor' => '0,200,83',
                        'beauty','barber' => '255,109,0',
                        'sports','activity'=> '255,214,0',
                        'consultant' => '41,121,255',
                        'training' => '124,58,237',
                        default => '26,35,126'
                    };
                    [$cr,$cg,$cb] = explode(',', $rgbStr);

                    if ($vendor->profile_image) {
                        $img = $vendor->profile_image;
                    } elseif (in_array($vType,['health','doctor'])) {
                        $img = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?q=80&w=600&auto=format&fit=crop';
                    } elseif (in_array($vType,['beauty','barber'])) {
                        $img = 'https://images.unsplash.com/photo-1560066984-138dadb4c035?q=80&w=600&auto=format&fit=crop';
                    } elseif (in_array($vType,['sports','activity'])) {
                        $img = 'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?q=80&w=600&auto=format&fit=crop';
                    } elseif ($vType === 'training') {
                        $img = 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=600&auto=format&fit=crop';
                    } else {
                        $img = 'https://images.unsplash.com/photo-1560250097-0b93528c311a?q=80&w=600&auto=format&fit=crop';
                    }
                    
                    $catCode = 'general';
                    if (in_array($vType, ['health','doctor'])) $catCode = 'doctor';
                    elseif (in_array($vType, ['beauty','barber'])) $catCode = 'barber';
                    elseif (in_array($vType, ['sports','activity'])) $catCode = 'sports';
                    elseif ($vType === 'consultant') $catCode = 'consultant';
                    elseif ($vType === 'training') $catCode = 'training';
                    
                    $routeUrl = route('vendor.show', $vendor->slug);
                    $priceStr = '₹' . number_format($vendor->service_fee);
                    $rating = 4.9; // mockup rating
                    $name = $vendor->business_name;
                    $address = $vendor->address ?? 'Premium Location, City Center';
                    $catLabel = $vTheme['label'] ?? ucfirst($vType);
                @endphp

                @if($catCode === 'barber')
                    {{-- 💈 1. Barber --}}
                    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-barber {{ $isOpen ? '' : 'bv-closed' }}" style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};">
                        <div class="bv-card-barber-img-wrap">
                            <img src="{{ $img }}" alt="{{ $name }}" loading="lazy">
                            <div class="bv-card-barber-hover-overlay">
                                <h3 style="color:#fff; font-size:20px; font-weight:800; margin:0 0 4px;">{{ $name }}
                                   <svg class="bv-card-verified" width="16" height="16" style="display:inline; color:#38bdf8;" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                   </svg>
                                </h3>
                                <div style="display:flex; align-items:center; gap:6px; font-size:13px; font-weight:700; color:#ffab40; margin-bottom:8px;">
                                    ★ {{ $rating }}
                                </div>
                                <p class="bv-card-barber-desc" style="font-size:12px; color:rgba(255,255,255,0.7);">Expert grooming & styling services.</p>
                            </div>
                        </div>
                        <div class="bv-card-barber-price-bar">
                            <div style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,0.5);">Starts From</div>
                            <div style="font-size:18px; font-weight:900; color:#fff;">{{ $priceStr }}</div>
                        </div>
                    </a>

                @elseif($catCode === 'consultant')
                    {{-- 🧑💼 2. Consultant --}}
                    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-consultant {{ $isOpen ? '' : 'bv-closed' }}" style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};">
                        <div class="bv-card-consultant-img-wrap">
                            <img src="{{ $img }}" alt="{{ $name }}" loading="lazy">
                            <div class="bv-card-consultant-overlay">
                                <h3 style="color:#fff; font-size:20px; font-weight:800; margin:0 0 4px;">{{ $name }}</h3>
                                <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                                    <div style="font-size:13px; font-weight:600; color:var(--c1);">{{ $catLabel }}</div>
                                    <div style="display:flex; align-items:center; gap:4px; font-size:13px; font-weight:700; color:#fff; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); padding:4px 8px; border-radius:8px;">
                                        <span style="color:#ffab40;">★</span> {{ $rating }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bv-card-consultant-price-bar">
                            <div>
                                <div style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:rgba(255,255,255,0.4); margin-bottom:2px;">Session</div>
                                <div style="font-size:18px; font-weight:900; color:#fff;">{{ $priceStr }}</div>
                            </div>
                            <div style="width:36px; height:36px; border-radius:12px; background:rgba(var(--cr),var(--cg),var(--cb),0.15); display:flex; align-items:center; justify-content:center; color:var(--c1);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </a>

                @elseif($catCode === 'doctor')
                    {{-- 🩺 4. Doctor --}}
                    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-doctor {{ $isOpen ? '' : 'bv-closed' }}" style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};">
                        <div class="bv-card-doctor-img-wrap">
                            <img src="{{ $img }}" alt="{{ $name }}" loading="lazy">
                            <div class="bv-card-doctor-overlay">
                                <h3 style="color:#fff; font-size:20px; font-weight:800; margin:0 0 4px;">{{ $name }}</h3>
                                <div style="display:flex; align-items:center; gap:4px; font-size:12px; color:rgba(255,255,255,0.8); margin-bottom:8px;">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    {{ $address }}
                                </div>
                                <div style="font-size:13px; font-weight:600; color:var(--c1);">{{ $catLabel }}</div>
                            </div>
                            <div style="position:absolute; top:12px; right:12px; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); padding:4px 8px; border-radius:8px; font-size:12px; font-weight:700; color:#fff; display:flex; align-items:center; gap:4px;">
                                <span style="color:#f59e0b;">★</span> {{ $rating }}
                            </div>
                        </div>
                        <div class="bv-card-doctor-price-bar">
                            <div style="font-size:12px; font-weight:800; color:rgba(255,255,255,0.5); text-transform:uppercase; letter-spacing:.05em;">Consultation</div>
                            <div style="font-size:20px; font-weight:900; color:#fff;">{{ $priceStr }}</div>
                        </div>
                    </a>
                
                @elseif($catCode === 'sports')
                    {{-- 🏃 3. Activity / Sports --}}
                    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-sports {{ $isOpen ? '' : 'bv-closed' }}" style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};">
                        <img src="{{ $img }}" alt="{{ $name }}" loading="lazy">
                        <div class="bv-card-sports-overlay">
                            <span style="display:inline-block; background:rgba(var(--cr),var(--cg),var(--cb),0.9); backdrop-filter:blur(4px); color:#000; font-size:10px; font-weight:900; padding:6px 12px; border-radius:8px; align-self:flex-start; margin-bottom:12px; text-transform:uppercase; letter-spacing:.05em;">
                                {{ $catLabel }}
                            </span>
                            <h3 style="color:#fff; font-size:24px; font-weight:900; margin:0 0 8px; line-height:1.1;">{{ $name }}</h3>
                            <div style="display:flex; align-items:center; gap:6px; font-size:13px; color:rgba(255,255,255,0.8); margin-bottom:20px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $address }}</span>
                            </div>
                            <div style="display:flex; align-items:center; justify-content:space-between; background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.2); backdrop-filter:blur(16px); -webkit-backdrop-filter:blur(16px); padding:14px; border-radius:14px;">
                                <div>
                                    <div style="font-size:11px; font-weight:800; text-transform:uppercase; color:rgba(255,255,255,0.6); letter-spacing:.05em;">Entry / Pass</div>
                                    <div style="font-size:20px; font-weight:900; color:#fff;">{{ $priceStr }}</div>
                                </div>
                                <div style="width:40px; height:40px; background:var(--c1); border-radius:50%; display:flex; align-items:center; justify-content:center; color:#000; box-shadow:0 6px 16px rgba(var(--cr),var(--cg),var(--cb),0.4);">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            </div>
                        </div>
                    </a>
                
                @elseif($catCode === 'training')
                    {{-- 🎓 5. Training & Skills --}}
                    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-training {{ $isOpen ? '' : 'bv-closed' }}" style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};">
                        <div class="bv-card-training-img-wrap">
                            <img src="{{ $img }}" alt="{{ $name }}" loading="lazy">
                            <div class="bv-card-training-overlay">
                                <h3 style="color:#fff; font-size:20px; font-weight:800; margin:0 0 4px;">{{ $name }}</h3>
                                <div style="display:flex; justify-content:space-between; align-items:flex-end;">
                                    <div style="font-size:13px; font-weight:600; color:var(--c1);">{{ $catLabel }}</div>
                                    <div style="display:flex; align-items:center; gap:4px; font-size:13px; font-weight:700; color:#fff; background:rgba(0,0,0,0.6); backdrop-filter:blur(4px); padding:4px 8px; border-radius:8px;">
                                        <span style="color:#ffab40;">★</span> {{ $rating }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bv-card-training-price-bar">
                            <div>
                                <div style="display:flex; align-items:center; gap:8px; font-size:11px; font-weight:700; letter-spacing:.05em; color:rgba(255,255,255,0.4); margin-bottom:2px;">
                                    <span>🏅 ALL LEVELS</span>
                                </div>
                                <div style="font-size:18px; font-weight:900; color:#fff;">{{ $priceStr }} <span style="font-size:10px; font-weight:600; color:rgba(255,255,255,0.4);">/ SESSION</span></div>
                            </div>
                            <div style="width:36px; height:36px; border-radius:12px; background:linear-gradient(135deg,var(--c1),var(--c2)); display:flex; align-items:center; justify-content:center; color:#fff; box-shadow:0 4px 12px rgba(var(--cr),var(--cg),var(--cb),0.4);">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                            </div>
                        </div>
                    </a>

                @else
                    {{-- 🧪 6. General / Vendor --}}
                    <a href="{{ $routeUrl }}" class="bv-dynamic-card bv-card-general {{ $isOpen ? '' : 'bv-closed' }}" style="--c1:{{ $c1 }};--c2:{{ $c2 }};--cr:{{ $cr }};--cg:{{ $cg }};--cb:{{ $cb }};">
                        <img src="{{ $img }}" alt="{{ $name }}" loading="lazy">
                        <h3 style="color:#fff; font-size:17px; font-weight:800; margin:0 0 4px;">{{ $name }}</h3>
                        <p style="font-size:12px; color:rgba(255,255,255,0.5); margin:0 0 16px;">Professional service provider</p>
                        
                        <div style="margin-top:auto; width:100%;">
                            <div style="font-size:16px; font-weight:900; color:#fff; margin-bottom:12px;">{{ $priceStr }}</div>
                            <div style="width:100%; border-radius:8px; background:linear-gradient(135deg, var(--c1), var(--c2)); color:#fff; font-size:13px; font-weight:800; padding:10px; display:inline-block; text-align:center;">
                                View Profile
                            </div>
                        </div>
                    </a>
                @endif

            @empty
                <div style="grid-column:1/-1; padding:80px 0; text-align:center;">
                    <div style="font-size:5rem; opacity:.2;">📭</div>
                    <h3 style="color:#fff; font-size:2rem; margin:24px 0 12px;">No Experts Found</h3>
                    <p style="color:rgba(255,255,255,.4);">Try adjusting your search or filters.</p>
                    <a href="{{ route('home') }}" style="display:inline-block; margin-top:28px; background:linear-gradient(135deg,#ff6d00,#ffab40); color:#fff; font-weight:800; padding:14px 32px; border-radius:12px; text-decoration:none;">Reset Search</a>
                </div>
            @endforelse
        </div>

        @if($vendors->hasPages())
            <div style="margin-top:60px;">{{ $vendors->links() }}</div>
        @endif
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     BOOK IN 3 EASY STEPS
═══════════════════════════════════════════════════════ --}}
<section class="bv-steps-section">
    <div style="text-align:center; margin-bottom:60px;">
        <h2 style="font-size:2.4rem; font-weight:900; color:#fff; letter-spacing:-.02em; margin:0 0 10px;">
            Book in <span style="color:#ff8c42; font-style:italic;">3 Easy Steps</span>
        </h2>
        <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.35em; color:rgba(255,255,255,.25);">Your professional journey starts here</p>
    </div>

    <div class="bv-steps-grid">

        {{-- Step 1: Find & Filter — map+magnifier 3D illustration --}}
        <div class="bv-step-card">
            <div class="bv-step-icon-wrap">
                <div class="bv-step-num">1</div>
                {{-- 3D Map + Magnifier SVG illustration --}}
                <svg width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- base map -->
                    <rect x="10" y="20" width="70" height="52" rx="8" fill="#1a2657" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
                    <path d="M10 28h70" stroke="rgba(255,255,255,.08)" stroke-width="1"/>
                    <!-- fold corner -->
                    <path d="M68 20 L80 32 L68 32 Z" fill="rgba(255,255,255,.1)"/>
                    <!-- map lines -->
                    <path d="M22 42 Q35 36 48 44 Q62 52 75 44" stroke="#00c853" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <path d="M22 54 Q38 48 52 56 Q64 62 75 56" stroke="rgba(255,255,255,.2)" stroke-width="1.5" fill="none" stroke-linecap="round"/>
                    <!-- pin -->
                    <circle cx="48" cy="44" r="5" fill="#ff6d00"/>
                    <line x1="48" y1="49" x2="48" y2="58" stroke="#ff6d00" stroke-width="2" stroke-linecap="round"/>
                    <!-- magnifier -->
                    <circle cx="65" cy="62" r="12" fill="rgba(26,35,100,.9)" stroke="rgba(255,255,255,.2)" stroke-width="1.5"/>
                    <circle cx="62" cy="59" r="7" fill="none" stroke="rgba(255,255,255,.6)" stroke-width="2"/>
                    <line x1="67" y1="64" x2="73" y2="70" stroke="rgba(255,255,255,.6)" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h3 class="bv-step-title">Find &amp; Filter</h3>
            <p class="bv-step-desc">Search for top-tier professionals in your area that fulfill your specific needs.</p>
        </div>

        {{-- Step 2: Choose Easy — calendar + checkmark 3D illustration --}}
        <div class="bv-step-card">
            <div class="bv-step-icon-wrap">
                <div class="bv-step-num">2</div>
                {{-- 3D Calendar SVG illustration --}}
                <svg width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- calendar body -->
                    <rect x="12" y="22" width="66" height="54" rx="8" fill="#1a2657" stroke="rgba(255,255,255,.15)" stroke-width="1"/>
                    <!-- header -->
                    <rect x="12" y="22" width="66" height="20" rx="8" fill="#2979ff"/>
                    <rect x="12" y="34" width="66" height="8" fill="#2979ff"/>
                    <!-- calendar rings -->
                    <rect x="28" y="16" width="5" height="14" rx="2.5" fill="rgba(255,255,255,.5)"/>
                    <rect x="57" y="16" width="5" height="14" rx="2.5" fill="rgba(255,255,255,.5)"/>
                    <!-- header text -->
                    <text x="45" y="37" text-anchor="middle" fill="white" font-size="9" font-weight="800" font-family="sans-serif">APRIL 2025</text>
                    <!-- grid dots -->
                    <circle cx="26" cy="55" r="3" fill="rgba(255,255,255,.15)"/>
                    <circle cx="38" cy="55" r="3" fill="rgba(255,255,255,.15)"/>
                    <circle cx="50" cy="55" r="3" fill="rgba(255,255,255,.15)"/>
                    <circle cx="62" cy="55" r="3" fill="rgba(255,255,255,.15)"/>
                    <circle cx="26" cy="66" r="3" fill="rgba(255,255,255,.15)"/>
                    <circle cx="38" cy="66" r="3" fill="rgba(255,255,255,.15)"/>
                    <!-- highlighted date -->
                    <circle cx="50" cy="66" r="7" fill="#ff6d00"/>
                    <text x="50" y="69.5" text-anchor="middle" fill="white" font-size="8" font-weight="900" font-family="sans-serif">15</text>
                    <circle cx="62" cy="66" r="3" fill="rgba(255,255,255,.15)"/>
                    <!-- checkmark badge -->
                    <circle cx="70" cy="32" r="10" fill="#00c853" stroke="#0a0f2c" stroke-width="2"/>
                    <path d="M64 32 L68 36 L76 28" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="bv-step-title">Choose Easy</h3>
            <p class="bv-step-desc">See detailed ratings and reviews, then book the best expert instantly.</p>
        </div>

        {{-- Step 3: Confirm & Go — ticket/pass 3D illustration --}}
        <div class="bv-step-card">
            <div class="bv-step-icon-wrap">
                <div class="bv-step-num">3</div>
                {{-- 3D Ticket SVG illustration --}}
                <svg width="90" height="90" viewBox="0 0 90 90" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <!-- ticket body -->
                    <rect x="8" y="28" width="74" height="38" rx="8" fill="#ff6d00"/>
                    <!-- lighter top half -->
                    <rect x="8" y="28" width="74" height="19" rx="8" fill="#ff8c3a"/>
                    <rect x="8" y="38" width="74" height="9" fill="#ff8c3a"/>
                    <!-- perforation notches -->
                    <circle cx="8" cy="47" r="6" fill="#0a0f2c"/>
                    <circle cx="82" cy="47" r="6" fill="#0a0f2c"/>
                    <!-- dashed line -->
                    <line x1="20" y1="47" x2="70" y2="47" stroke="rgba(255,255,255,.35)" stroke-width="1.5" stroke-dasharray="4 3"/>
                    <!-- top section content -->
                    <text x="30" y="41" fill="white" font-size="8" font-weight="900" font-family="sans-serif">CONFIRMED</text>
                    <circle cx="18" cy="37" r="5" fill="rgba(255,255,255,.25)"/>
                    <path d="M15 37 L17 39 L21 35" stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <!-- bottom section -->
                    <text x="26" y="60" fill="rgba(255,255,255,.8)" font-size="7" font-family="sans-serif" font-weight="700">APPT #2025-04-15</text>
                    <!-- star accent -->
                    <circle cx="72" cy="34" r="9" fill="rgba(255,255,255,.15)" stroke="rgba(255,255,255,.3)" stroke-width="1"/>
                    <text x="72" y="37.5" text-anchor="middle" font-size="11" fill="white">★</text>
                    <!-- lightning bolt -->
                    <path d="M45 10 L38 22 L44 22 L40 34 L52 18 L46 18 Z" fill="#ffab40" stroke="rgba(255,255,255,.3)" stroke-width="1"/>
                </svg>
            </div>
            <h3 class="bv-step-title">Confirm &amp; Go</h3>
            <p class="bv-step-desc">Get instant confirmation and reminders for your professional appointment.</p>
        </div>
    </div>

    {{-- Ambient glow --}}
    <div style="position:absolute; top:20%; right:-10%; width:500px; height:500px; background:rgba(255,109,0,.05); border-radius:50%; filter:blur(100px); pointer-events:none;"></div>
</section>

{{-- ═══════════════════════════════════════════════════════
     GROW YOUR BUSINESS CTA
═══════════════════════════════════════════════════════ --}}
<section class="bv-cta-section">
    <div class="bv-cta-glow"></div>
    <div style="position:relative; z-index:2;">
        <div class="bv-cta-badge">
            <span style="width:6px;height:6px;border-radius:50%;background:#ff6d00;display:inline-block;"></span>
            Join With Bookai Platform
        </div>
        <h2 class="bv-cta-title">
            GROW YOUR <br><span class="bv-cta-accent">BUSINESS</span> WITH US
        </h2>
        <p class="bv-cta-desc">
            Are you a professional? Join us to get more bookings and grow your client base with our advanced tools.
        </p>
        <a href="/register/vendor" class="bv-cta-btn">
            Join as a Professional
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
        </a>
    </div>
    {{-- Decorative star --}}
    <div style="position:absolute; bottom:40px; right:60px; width:60px; height:60px; opacity:.15;">
        <svg viewBox="0 0 100 100" fill="white"><polygon points="50,5 61,35 95,35 68,57 79,91 50,70 21,91 32,57 5,35 39,35"/></svg>
    </div>
</section>

</div>{{-- .bv-page --}}

{{-- ── Counter animation script ─────────────────────────────── --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const counters = document.querySelectorAll('[data-counter]');
    const animate  = (el) => {
        const target   = parseFloat(el.dataset.target);
        const decimals = parseInt(el.dataset.decimals) || 0;
        const suffix   = el.dataset.suffix || '';
        const start    = performance.now();
        const duration = 2000;
        const tick = (now) => {
            const p = Math.min((now - start) / duration, 1);
            const e = 1 - Math.pow(1 - p, 4);
            el.innerText = (e * target).toFixed(decimals) + suffix;
            if (p < 1) requestAnimationFrame(tick);
            else el.innerText = target.toFixed(decimals) + suffix;
        };
        requestAnimationFrame(tick);
    };
    new IntersectionObserver((entries) => {
        entries.forEach(en => { if (en.isIntersecting) { animate(en.target); observer.unobserve(en.target); } });
    }, { threshold: 0.1 }).observe;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(en => { if (en.isIntersecting) { animate(en.target); observer.unobserve(en.target); } });
    }, { threshold: 0.1 });
    counters.forEach(c => observer.observe(c));
});
</script>

</x-app-layout>
