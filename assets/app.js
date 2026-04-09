import './stimulus_bootstrap.js';
import './styles/app.css';
import './styles/topseller_header_footer.css'
import './styles/cart_checkout.css';
import { gsap } from 'gsap';
import 'bootstrap/dist/css/bootstrap.min.css';

/* Dropdown account — ouverture au survol sur desktop */
document.querySelectorAll('.dropdown').forEach(drop => {
  if (window.innerWidth >= 992) {
    drop.addEventListener('mouseenter', () => {
      drop.querySelector('[data-bs-toggle="dropdown"]')
          ?.dispatchEvent(new Event('click'));
    });
    drop.addEventListener('mouseleave', () => {
      const menu = drop.querySelector('.dropdown-menu');
      if (menu) menu.classList.remove('show');
      drop.querySelector('[data-bs-toggle="dropdown"]')
          ?.setAttribute('aria-expanded', 'false');
    });
  }
});

/* ═══════════════════════════════════════════════════════════════════════════
   PROTECTION ANTI-DOUBLE-BIND
   ═══════════════════════════════════════════════════════════════════════════ */
let cartAbortController    = null;
let productAbortController = null;

/* ══════════════════════════════════════════════════════════════════════════
   PAGE PRODUIT — QUANTITÉ
   ══════════════════════════════════════════════════════════════════════════ */
let currentMaxStock = null;

function setCurrentStock(stock) {
  const qtyEl    = document.getElementById('qtyVal');
  const qtyInput = document.getElementById('qtyInput');
  const baseStock = window.baseProductData ? parseInt(window.baseProductData.stock, 10) : Infinity;
  const s = parseInt(stock, 10);
  currentMaxStock = !Number.isNaN(s) && s > 0 ? s : baseStock;
  if (qtyEl && qtyInput) {
    let val = parseInt(qtyInput.value || qtyEl.textContent || '1', 10);
    if (val < 1) val = 1;
    if (val > currentMaxStock) val = currentMaxStock;
    qtyEl.textContent = val;
    qtyInput.value    = val;
  }
}

function changeQty(delta) {
  const el    = document.getElementById('qtyVal');
  const input = document.getElementById('qtyInput');
  if (!el || !input) return;
  const baseStock = window.baseProductData ? parseInt(window.baseProductData.stock, 10) : Infinity;
  const max = currentMaxStock != null ? currentMaxStock : baseStock;
  let val = parseInt(input.value || el.textContent || '1', 10) + delta;
  if (val < 1) val = 1;
  if (val > max) val = max;
  el.textContent = val;
  input.value    = val;
}

window.setCurrentStock = setCurrentStock;
window.changeQty       = changeQty;

/* ══════════════════════════════════════════════════════════════════════════
   REVIEWS — Validation formulaire
   ══════════════════════════════════════════════════════════════════════════ */
function initReviewForm() {
  const reviewForm = document.getElementById('reviewForm');
  if (!reviewForm) return;
  reviewForm.addEventListener('submit', function (e) {
    if (!this.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
    this.classList.add('was-validated');
    const rated = document.querySelector('input[name="rating"]:checked');
    if (!rated) {
      e.preventDefault();
      const starRating = document.getElementById('starRating');
      if (starRating) starRating.style.outline = '2px solid #e63535';
    }
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   GALERIE — Helpers
   ══════════════════════════════════════════════════════════════════════════ */
function switchImage(thumb) {
  const main = document.getElementById('mainProductImage');
  if (!main) return;
  main.src = thumb.dataset.src;
  document.querySelectorAll('.nw-thumb').forEach(t => t.classList.remove('is-active'));
  thumb.classList.add('is-active');
}

/* ══════════════════════════════════════════════════════════════════════════
   TABS
   ══════════════════════════════════════════════════════════════════════════ */
function initTabs() {
  document.querySelectorAll('.nw-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.nw-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('d-none'));
      tab.classList.add('active');
      document.getElementById('tab-' + tab.dataset.tab)?.classList.remove('d-none');
    });
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   LOAD MORE REVIEWS (AJAX)
   ══════════════════════════════════════════════════════════════════════════ */
function initLoadMoreReviews() {
  const loadMoreBtn = document.getElementById('loadMoreBtn');
  if (!loadMoreBtn) return;
  loadMoreBtn.addEventListener('click', async () => {
    const page      = parseInt(loadMoreBtn.dataset.page) + 1;
    const productId = loadMoreBtn.dataset.product;
    loadMoreBtn.disabled    = true;
    loadMoreBtn.textContent = 'Loading…';
    try {
      const res  = await fetch(`/reviews/${productId}?page=${page}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      if (data.reviews && data.reviews.length > 0) {
        const grid = document.getElementById('reviewGrid');
        data.reviews.forEach(r => {
          const stars = Array.from({ length: 5 }, (_, i) =>
            `<span ${i >= r.rating ? 'class="nw-star-dim"' : ''}>★</span>`
          ).join('');
          grid.insertAdjacentHTML('beforeend', `
            <div class="col review-item">
              <article class="nw-reviewcard h-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="nw-stars" style="font-size:14px;">${stars}</span>
                  <button class="nw-kebab" aria-label="Options">···</button>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                  <strong style="font-size:14px;">${r.authorName}</strong>
                  <span class="nw-verified" title="Verified purchase">✓</span>
                </div>
                <p class="nw-review-text mb-2">"${r.comment}"</p>
                <div class="nw-review-date">Posted on ${r.date}</div>
              </article>
            </div>
          `);
        });
        loadMoreBtn.dataset.page    = page;
        loadMoreBtn.disabled        = false;
        loadMoreBtn.textContent     = 'Load More Reviews';
        if (!data.hasMore) loadMoreBtn.style.display = 'none';
      } else {
        loadMoreBtn.style.display = 'none';
      }
    } catch (e) {
      loadMoreBtn.disabled    = false;
      loadMoreBtn.textContent = 'Load More Reviews';
    }
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   FAQ — Toggle panels
   ══════════════════════════════════════════════════════════════════════════ */
function initFaqPanels() {
  document.querySelectorAll('.visible-pannel .toggle-icon').forEach(cross => {
    cross.addEventListener('click', function () {
      const height        = this.parentNode.parentNode.childNodes[3].scrollHeight;
      const currentChoice = this.parentNode.parentNode.childNodes[3];
      if (this.src.includes('plus')) {
        this.src = '/images/icons/dash.svg';
        gsap.to(currentChoice, { height: height + 40, duration: 0.3, opacity: 1, padding: '20px 15px' });
      } else if (this.src.includes('dash')) {
        this.src = '/images/icons/plus.svg';
        gsap.to(currentChoice, { height: 0, duration: 0.3, opacity: 0, padding: '0px 15px' });
      }
    });
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   GALERIE + LIGHTBOX
   ══════════════════════════════════════════════════════════════════════════ */
let galleryImages = [];
let lightboxIndex = 0;

function initProductGallery() {
  const main        = document.getElementById('mainProductImage');
  const thumbs      = Array.from(document.querySelectorAll('.js-product-thumb'));
  const moreBtn     = document.querySelector('.js-product-thumb-more');
  const moreImg     = moreBtn?.querySelector('.js-more-thumb-img');
  const moreCountEl = moreBtn?.querySelector('.js-more-thumb-count');
  if (!main || !thumbs.length) return;

  const VISIBLE_LIMIT = 6;
  const defaultSrc    = main.dataset.defaultSrc || main.src;
  const groups        = { default: [] };

  thumbs.forEach(thumb => {
    const cid = thumb.dataset.colorId || '';
    if (!cid) { groups.default.push(thumb); }
    else { if (!groups[cid]) groups[cid] = []; groups[cid].push(thumb); }
    thumb.addEventListener('mouseenter', () => switchImage(thumb));
    thumb.addEventListener('click', () => {
      if (!galleryImages.length) return;
      const idx = galleryImages.indexOf(thumb.dataset.src);
      openLightbox(idx >= 0 ? idx : 0);
    });
  });

  function applyGroup(colorId) {
    const key         = colorId ? String(colorId) : 'default';
    const groupThumbs = groups[key] || [];
    thumbs.forEach(t => t.classList.add('d-none'));
    const total = groupThumbs.length;
    if (total === 0) { main.src = defaultSrc; galleryImages = []; if (moreBtn) moreBtn.classList.add('d-none'); return; }
    if (total <= VISIBLE_LIMIT) {
      groupThumbs.forEach(t => t.classList.remove('d-none'));
      if (moreBtn) moreBtn.classList.add('d-none');
    } else {
      groupThumbs.slice(0, VISIBLE_LIMIT - 1).forEach(t => t.classList.remove('d-none'));
      if (moreBtn && moreImg && moreCountEl) {
        const refThumb          = groupThumbs[VISIBLE_LIMIT - 1];
        moreImg.src             = refThumb.dataset.src;
        moreCountEl.textContent = `+${Math.max(total - VISIBLE_LIMIT, 0)} plus`;
        moreBtn.classList.remove('d-none');
        moreBtn.onclick = () => { galleryImages = groupThumbs.map(t => t.dataset.src); openLightbox(0); };
      }
    }
    const mainThumb = groupThumbs.find(t => t.dataset.isMain === '1') || groupThumbs[0];
    if (mainThumb) { switchImage(mainThumb); } else { main.src = defaultSrc; }
    galleryImages = groupThumbs.map(t => t.dataset.src);
  }

  main.addEventListener('click', () => {
    if (!galleryImages.length) return;
    const idx = galleryImages.indexOf(main.src);
    openLightbox(idx >= 0 ? idx : 0);
  });

  window.applyGalleryGroup = applyGroup;
  applyGroup(null);

  const lb = document.getElementById('imageLightbox');
  if (lb) {
    lb.querySelector('.nw-lightbox-close')?.addEventListener('click', closeLightbox);
    lb.querySelector('.nw-lightbox-backdrop')?.addEventListener('click', closeLightbox);
    lb.querySelector('.nw-lightbox-prev')?.addEventListener('click', () => changeLightbox(-1));
    lb.querySelector('.nw-lightbox-next')?.addEventListener('click', () => changeLightbox(1));
  }
}

function openLightbox(index) {
  if (!galleryImages.length) return;
  lightboxIndex = index;
  const lb    = document.getElementById('imageLightbox');
  const img   = document.getElementById('lightboxImage');
  const cur   = document.getElementById('lightboxCurrent');
  const total = document.getElementById('lightboxTotal');
  if (!lb || !img) return;
  img.src = galleryImages[lightboxIndex];
  if (cur)   cur.textContent   = lightboxIndex + 1;
  if (total) total.textContent = galleryImages.length;
  lb.classList.remove('d-none');
  document.body.classList.add('nw-lightbox-open');
  gsap.fromTo('.nw-lightbox-inner', { opacity: 0, y: 40 }, { opacity: 1, y: 0, duration: 0.4, ease: 'power2.out' });
}

function closeLightbox() {
  const lb = document.getElementById('imageLightbox');
  if (!lb) return;
  gsap.to('.nw-lightbox-inner', {
    opacity: 0, y: 40, duration: 0.25, ease: 'power2.in',
    onComplete: () => { lb.classList.add('d-none'); document.body.classList.remove('nw-lightbox-open'); },
  });
}

function changeLightbox(delta) {
  if (!galleryImages.length) return;
  lightboxIndex = (lightboxIndex + delta + galleryImages.length) % galleryImages.length;
  const img = document.getElementById('lightboxImage');
  const cur = document.getElementById('lightboxCurrent');
  if (!img) return;
  gsap.to(img, {
    opacity: 0, duration: 0.15,
    onComplete() {
      img.src = galleryImages[lightboxIndex];
      if (cur) cur.textContent = lightboxIndex + 1;
      gsap.fromTo(img, { opacity: 0 }, { opacity: 1, duration: 0.2 });
    },
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   PRIX + TAILLES + COULEURS
   ══════════════════════════════════════════════════════════════════════════ */
function updatePriceDisplay(price, compareAt) {
  const priceEl    = document.querySelector('.js-product-price');
  const compareEl  = document.querySelector('.js-product-compare-at');
  const discountEl = document.querySelector('.js-discount-badge');
  if (priceEl && typeof price === 'number') priceEl.textContent = '$' + price.toFixed(2);
  let hasCompare = false;
  if (compareEl) {
    if (typeof compareAt === 'number' && compareAt > price) {
      compareEl.textContent = '$' + compareAt.toFixed(2);
      compareEl.classList.remove('d-none');
      hasCompare = true;
    } else { compareEl.classList.add('d-none'); }
  }
  if (discountEl) {
    if (hasCompare) {
      const pct = Math.round(((compareAt - price) / compareAt) * 100);
      discountEl.textContent = `-${pct}%`;
      discountEl.classList.remove('d-none');
    } else { discountEl.classList.add('d-none'); }
  }
}

function updateAvailableSizes(sizeIds) {
  const pills     = Array.from(document.querySelectorAll('.js-size-pill'));
  const sizeInput = document.querySelector('.js-size-input');
  if (!pills.length) return;
  const allowed = Array.isArray(sizeIds) ? (sizeIds.length > 0 ? sizeIds.map(Number) : []) : null;
  pills.forEach(pill => {
    const id      = parseInt(pill.dataset.sizeId, 10);
    const enabled = allowed === null || allowed.includes(id);
    pill.disabled = !enabled;
    pill.classList.toggle('is-disabled', !enabled);
    if (!enabled && pill.classList.contains('is-active')) {
      pill.classList.remove('is-active');
      if (sizeInput && sizeInput.value === String(id)) sizeInput.value = '';
    }
  });
}

function initSizePills() {
  const pills     = Array.from(document.querySelectorAll('.js-size-pill'));
  const sizeInput = document.querySelector('.js-size-input');
  if (!pills.length || !sizeInput) return;
  pills.forEach(pill => {
    pill.addEventListener('click', () => {
      if (pill.disabled) return;
      pills.forEach(p => p.classList.remove('is-active'));
      pill.classList.add('is-active');
      sizeInput.value = pill.dataset.sizeId;
      const cartSizeInput = document.getElementById('cartSizeId');
      if (cartSizeInput) cartSizeInput.value = pill.dataset.sizeId;
    });
  });
}

function initColorSwatches() {
  const container = document.getElementById('colorSwatches');
  if (!container) return;
  const swatches    = Array.from(container.querySelectorAll('.js-color-swatch'));
  const colorNameEl = document.getElementById('selectedColorName');
  const variantsScript = document.getElementById('colorVariantsData');
  let colorVariants = {};
  if (variantsScript) { try { colorVariants = JSON.parse(variantsScript.textContent || '{}'); } catch (e) {} }
  window.colorVariants   = colorVariants;
  window.baseProductData = window.baseProductData || { price: null, compareAtPrice: null, stock: null, sizeIds: [] };

  function applyColor(rawColorId, colorName) {
    const idStr = rawColorId ? String(rawColorId) : '';
    swatches.forEach(btn => btn.classList.toggle('is-selected', (btn.dataset.colorId || '') === idStr));
    if (colorNameEl) colorNameEl.textContent = colorName || '';
    const cartVariantInput = document.getElementById('cartVariantId');
    if (cartVariantInput) cartVariantInput.value = idStr;
    if (typeof window.applyGalleryGroup === 'function') window.applyGalleryGroup(idStr === '' ? null : idStr);
    const variant = idStr !== '' && window.colorVariants[idStr] ? window.colorVariants[idStr] : null;
    if (variant) {
      updatePriceDisplay(variant.price, variant.compareAtPrice);
      updateAvailableSizes(variant.sizeIds);
      window.setCurrentStock?.(variant.stock);
    } else {
      updatePriceDisplay(window.baseProductData.price, window.baseProductData.compareAtPrice);
      updateAvailableSizes(window.baseProductData.sizeIds);
      window.setCurrentStock?.(window.baseProductData.stock);
    }
  }

  swatches.forEach(btn => btn.addEventListener('click', () => applyColor(btn.dataset.colorId || '', btn.dataset.colorName || '')));
  const initialSwatch = swatches.find(s => (s.dataset.colorId || '') === '') || swatches[0];
  if (initialSwatch) applyColor(initialSwatch.dataset.colorId || '', initialSwatch.dataset.colorName || '');
}

function initImageZoom() {
  const container = document.querySelector('.js-main-zoom');
  if (!container) return;
  const img = container.querySelector('#mainProductImage');
  if (!img) return;
  container.addEventListener('mouseenter', () => img.classList.add('is-zoomed'));
  container.addEventListener('mousemove', (e) => {
    if (!img.classList.contains('is-zoomed')) return;
    const rect = container.getBoundingClientRect();
    img.style.transformOrigin = `${((e.clientX - rect.left) / rect.width) * 100}% ${((e.clientY - rect.top) / rect.height) * 100}%`;
  });
  container.addEventListener('mouseleave', () => { img.classList.remove('is-zoomed'); img.style.transformOrigin = ''; });
}

/* ══════════════════════════════════════════════════════════════════════════
   PANIER — Helpers AJAX
   ══════════════════════════════════════════════════════════════════════════ */
function updateTotalsDisplay(data) {
  const subtotalEl = document.getElementById('subtotalVal');
  const discountEl = document.getElementById('discountVal');
  const totalEl    = document.getElementById('totalVal');
  if (subtotalEl && data.subtotal !== undefined) subtotalEl.textContent = `$${Number(data.subtotal).toFixed(2)}`;
  if (discountEl && data.discount !== undefined) discountEl.textContent = data.discount > 0 ? `-$${Number(data.discount).toFixed(2)}` : '$0.00';
  if (totalEl    && data.total    !== undefined) totalEl.textContent    = `$${Number(data.total).toFixed(2)}`;
}

function updateItemCountDisplay() {
  let count = 0;
  document.querySelectorAll('.js-cart-qty').forEach(el => { count += parseInt(el.textContent || '0', 10); });
  const summaryItems = document.getElementById('summaryItems');
  if (summaryItems) summaryItems.textContent = count;
}

async function updateItemQty(itemId, delta) {
  const qtyEl = document.querySelector(`.js-cart-qty[data-item-id="${itemId}"]`);
  if (!qtyEl) return;
  const currentQty = parseInt(qtyEl.textContent || '1', 10);
  if (currentQty + delta < 1) return;
  try {
    const res = await fetch(`/cart/item/${itemId}/update`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ delta }),
    });
    if (!res.ok) return;
    const data = await res.json();
    qtyEl.textContent = data.quantity;
    const lineTotalEl = document.querySelector(`.js-line-total[data-item-id="${itemId}"]`);
    if (lineTotalEl && data.lineTotal !== undefined) lineTotalEl.textContent = `$${Number(data.lineTotal).toFixed(2)}`;
    updateTotalsDisplay(data);
    updateItemCountDisplay();
  } catch (e) { console.error('[Cart] updateItemQty error:', e); }
}

async function removeItem(itemId) {
  const row = document.getElementById(`cart-row-${itemId}`);
  if (row) {
    row.style.transition = 'opacity 0.3s, transform 0.3s';
    row.style.opacity    = '0';
    row.style.transform  = 'translateX(-8px)';
    setTimeout(() => row.remove(), 320);
  }
  try {
    const res = await fetch(`/cart/item/${itemId}/delete`, { method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' } });
    if (!res.ok) return;
    const data = await res.json();
    updateTotalsDisplay(data);
    setTimeout(updateItemCountDisplay, 350);
  } catch (e) { console.error('[Cart] removeItem error:', e); }
}

async function applyCoupon() {
  const input = document.querySelector('.js-coupon-code');
  const msgEl = document.getElementById('couponMessage');
  const code  = input ? input.value.trim() : '';
  if (!msgEl) return;
  try {
    const res  = await fetch('/cart/coupon/apply', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body: JSON.stringify({ code }),
    });
    const data = await res.json();
    if (!res.ok || data.error) { msgEl.className = 'mb-2 small text-danger'; msgEl.textContent = data.error || 'Une erreur est survenue.'; return; }
    msgEl.className   = 'mb-2 small text-success';
    msgEl.textContent = data.message || 'Coupon appliqué !';
    updateTotalsDisplay(data);
  } catch (e) { if (msgEl) { msgEl.className = 'mb-2 small text-danger'; msgEl.textContent = 'Erreur réseau.'; } }
}

/* ══════════════════════════════════════════════════════════════════════════
   PANIER — Init
   ══════════════════════════════════════════════════════════════════════════ */
function initCartPage() {
  if (cartAbortController) cartAbortController.abort();
  cartAbortController = new AbortController();
  const { signal } = cartAbortController;
  document.querySelectorAll('.js-cart-increase').forEach(btn => btn.addEventListener('click', () => updateItemQty(btn.dataset.itemId, +1), { signal }));
  document.querySelectorAll('.js-cart-decrease').forEach(btn => btn.addEventListener('click', () => updateItemQty(btn.dataset.itemId, -1), { signal }));
  document.querySelectorAll('.js-cart-remove').forEach(btn   => btn.addEventListener('click', () => removeItem(btn.dataset.itemId), { signal }));
  const applyBtn    = document.querySelector('.js-apply-coupon');
  const clearBtn    = document.querySelector('.js-clear-cart');
  const couponInput = document.querySelector('.js-coupon-code');
  if (applyBtn)    applyBtn.addEventListener('click', applyCoupon, { signal });
  if (couponInput) couponInput.addEventListener('keydown', (e) => { if (e.key === 'Enter') { e.preventDefault(); applyCoupon(); } }, { signal });
  if (clearBtn)    clearBtn.addEventListener('click', () => document.querySelectorAll('.js-cart-remove').forEach(btn => removeItem(btn.dataset.itemId)), { signal });
}

/* ══════════════════════════════════════════════════════════════════════════
   PAGE PRODUIT — Init
   ══════════════════════════════════════════════════════════════════════════ */
function initSingleProductPage() {
  if (!document.body.classList.contains('page-product-show')) return;
  if (productAbortController) productAbortController.abort();
  productAbortController = new AbortController();
  initProductGallery();
  initColorSwatches();
  initSizePills();
  initImageZoom();
  initReviewForm();
  initTabs();
  initFaqPanels();
  initLoadMoreReviews();
  if (window.baseProductData && typeof window.setCurrentStock === 'function') {
    window.setCurrentStock(window.baseProductData.stock);
  }
}

/* ══════════════════════════════════════════════════════════════════════════
   HOME — Hover image swap
   ══════════════════════════════════════════════════════════════════════════ */
function initCardHover() {
  document.querySelectorAll('.js-card-wrap').forEach(wrap => {
    if (wrap.dataset.hoverInit) return;
    wrap.dataset.hoverInit = '1';

    const img = wrap.querySelector('.card-img-main');
    if (!img) return;

    const srcDefault = img.dataset.srcDefault;
    const srcHover   = img.dataset.srcHover;
    if (!srcHover) return;

    // Précharger l'image hover pour éviter le flash blanc
    const preload  = new Image();
    preload.src    = srcHover;

    // Créer une 2ème image superposée (opacity crossfade CSS-only, sans setTimeout)
    const imgHover = document.createElement('img');
    imgHover.src   = srcHover;
    imgHover.alt   = img.alt;
    imgHover.style.cssText = `
      position:absolute;
      inset:0;
      width:100%;
      height:100%;
      object-fit:cover;
      opacity:0;
      transition:opacity .28s ease;
      pointer-events:none;
    `;

    // S'assure que le wrapper est positionné
    wrap.style.position = 'relative';
    wrap.appendChild(imgHover);

    wrap.addEventListener('mouseenter', () => { imgHover.style.opacity = '1'; });
    wrap.addEventListener('mouseleave', () => { imgHover.style.opacity = '0'; });
  });
}

/* ══════════════════════════════════════════════════════════════
   HOME — TOP SELLING slider (1 item par clic, infini dans la liste)
══════════════════════════════════════════════════════════════ */
function initTopSellingSlider() {
  const track   = document.getElementById('tsTrack');
  const btnPrev = document.getElementById('tsArrowPrev');
  const btnNext = document.getElementById('tsArrowNext');
  if (!track || !btnPrev || !btnNext) return;

  const items      = Array.from(track.querySelectorAll('.nw-ts-item'));
  const total      = items.length;
  if (total === 0) return;

  // Calcule combien d'items sont visibles selon la largeur
  function getVisible() {
    const vw = window.innerWidth;
    if (vw <= 575) return 1;
    if (vw <= 991) return 2;
    return 4;
  }

  let current = 0; // index du premier item visible

  function getItemWidth() {
    // largeur d'un item + gap
    const item = items[0];
    const gap  = 20;
    return item.getBoundingClientRect().width + gap;
  }

  function goTo(idx) {
    const visible = getVisible();
    const maxIdx  = Math.max(0, total - visible);
    current       = Math.max(0, Math.min(idx, maxIdx));

    track.style.transform = `translateX(-${current * getItemWidth()}px)`;
    btnPrev.disabled      = current === 0;
    btnNext.disabled      = current >= maxIdx;
  }

  btnPrev.addEventListener('click', () => goTo(current - 1));
  btnNext.addEventListener('click', () => goTo(current + 1));

  // Recalcule au resize
  window.addEventListener('resize', () => goTo(current));

  goTo(0);
}

/* ══════════════════════════════════════════════════════════════════════════
   HOME — View All AJAX
   ══════════════════════════════════════════════════════════════════════════ */
function initViewAll() {
  const btn  = document.getElementById('viewAllBtn');
  const grid = document.getElementById('arrivalsGrid');
  if (!btn || !grid) return;

  btn.addEventListener('click', async () => {
    btn.disabled    = true;
    btn.textContent = 'Loading…';
    try {
      const res  = await fetch('/home/more-arrivals', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      if (!data.length) { btn.style.display = 'none'; return; }

      data.forEach((p, i) => {
        const avg   = p.ratingAverage ? Math.floor(parseFloat(p.ratingAverage)) : 0;
        const stars = Array.from({ length: 5 }, (_, idx) =>
          `<span class="star ${idx < avg ? 'filled' : 'empty'}">★</span>`
        ).join('');

        const html = `
          <div class="col-6 col-md-3" style="opacity:0;transform:translateY(16px);transition:opacity .4s ease ${i * 80}ms,transform .4s ease ${i * 80}ms">
            <div class="product-card">
              <div class="card-image-wrapper js-card-wrap">
                <a href="/product/${p.slug}">
                  <img
                    class="card-img-main"
                    src="${p.mainImage}"
                    data-src-default="${p.mainImage}"
                    data-src-hover="${p.secondaryImage ?? ''}"
                    alt="${p.name}"
                    style="transition:opacity .25s ease;"
                  />
                </a>
                ${p.compareAtPrice && p.compareAtPrice > p.price
                  ? `<span class="badge-sale">-${Math.round((p.compareAtPrice - p.price) / p.compareAtPrice * 100)}%</span>`
                  : ''}
              </div>
              <button class="add-to-cart">
                <svg viewBox="0 0 24 24"><path d="M17 18c-1.1 0-1.99.9-1.99 2S15.9 22 17 22s2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96C5 16.1 6.9 18 9 18h12v-2H9.42c-.14 0-.25-.11-.25-.25l.03-.12.9-1.63H19c.75 0 1.41-.41 1.75-1.03l3.58-6.49A1 1 0 0023.44 5H5.21l-.94-2H1zm8 16c-1.1 0-1.99.9-1.99 2S7.9 22 9 22s2-.9 2-2-.9-2-2-2z"/></svg>
                Add to Cart
              </button>
              <div class="card-body mt-2">
                <p class="product-name"><a class="text-black" href="/product/${p.slug}">${p.name}</a></p>
                <div class="price-wrapper">
                  <span class="price-current">$${parseFloat(p.price).toFixed(2)}</span>
                  ${p.compareAtPrice && p.compareAtPrice > p.price
                    ? `<span class="price-old">$${parseFloat(p.compareAtPrice).toFixed(2)}</span>`
                    : ''}
                </div>
                <div class="rating pb-2">
                  <div class="stars">${stars}</div>
                  <span class="reviews-count">(${p.ratingCount ?? 0})</span>
                </div>
              </div>
            </div>
          </div>`;
        grid.insertAdjacentHTML('beforeend', html);
      });

      // Anime l'entrée
      requestAnimationFrame(() => {
        grid.querySelectorAll('.col-6[style*="opacity:0"]').forEach(el => {
          el.style.opacity   = '1';
          el.style.transform = 'translateY(0)';
        });
      });

      initCardHover(); // active le hover sur les nouvelles cartes
      btn.style.display = 'none';

    } catch (err) {
      console.error('[ViewAll]', err);
      btn.disabled    = false;
      btn.textContent = 'View All';
    }
  });
}

/* ══════════════════════════════════════════════════════════════════════════
   HOME — Slider catégories
   ══════════════════════════════════════════════════════════════════════════ */
function initCategoryScroll() {
  const track   = document.getElementById('catTrack');
  const btnPrev = document.getElementById('catPrev');
  const btnNext = document.getElementById('catNext');
  if (!track || !btnPrev || !btnNext) return;

  const pages = Array.from(track.querySelectorAll('.nw-cat-page'));
  const total = pages.length;

  // Cas : une seule page ou zéro → flèches inutiles
  if (total <= 1) {
    btnPrev.disabled = true;
    btnNext.disabled = true;
    return;
  }

  let current = 0;

  function goTo(idx) {
    current               = Math.max(0, Math.min(idx, total - 1));
    // Chaque page fait exactement la largeur du viewport → translateX en %
    track.style.transform = `translateX(-${current * 100}%)`;
    btnPrev.disabled      = current === 0;
    btnNext.disabled      = current === total - 1;
  }

  btnPrev.addEventListener('click', () => goTo(current - 1));
  btnNext.addEventListener('click', () => goTo(current + 1));

  goTo(0); // état initial : gauche bloquée, droite active si > 1 page
}

/* ══════════════════════════════════════════════════════════════
   SHOP — Filtres AJAX
══════════════════════════════════════════════════════════════ */
function initShopFilters() {
  if (!document.getElementById('productsGrid')) return;

  const grid       = document.getElementById('productsGrid');
  const countEl    = document.getElementById('productCount');
  const sortSelect = document.getElementById('sortSelect');

  // ── Collecte tous les filtres actifs ───────────────────────
  function collectFilters() {
    const params = new URLSearchParams();

    // Prix
    const min = document.getElementById('sliderMin')?.value;
    const max = document.getElementById('sliderMax')?.value;
    if (min) params.set('min_price', min);
    if (max) params.set('max_price', max);

    // Couleurs
    document.querySelectorAll('.js-filter-color:checked').forEach(cb => {
      params.append('colors[]', cb.value);
    });

    // Tailles
    document.querySelectorAll('.js-filter-size:checked').forEach(cb => {
      params.append('sizes[]', cb.value);
    });

    // Marque (radio)
    const brand = document.querySelector('.js-filter-brand:checked');
    if (brand) params.set('brand', brand.value);

    // Statut
    const onSale  = document.getElementById('filterOnSale');
    const inStock = document.getElementById('filterInStock');
    if (onSale?.checked)  params.set('on_sale',  '1');
    if (inStock?.checked) params.set('in_stock', '1');

    // Tri
    if (sortSelect) params.set('sort', sortSelect.value);

    return params;
  }

  // ── Requête AJAX ───────────────────────────────────────────
  let abortCtrl = null;

  async function applyFilters() {
    if (abortCtrl) abortCtrl.abort();
    abortCtrl = new AbortController();

    grid.classList.add('is-loading');

    try {
      const params = collectFilters();
      const res    = await fetch(`/shop?${params.toString()}`, {
        headers:{ 'X-Requested-With': 'XMLHttpRequest' },
        signal: abortCtrl.signal,
      });
      const data = await res.json();

      grid.innerHTML = data.html;
      if (countEl) {
        countEl.textContent = data.count + ' product' + (data.count !== 1 ? 's' : '');
      }

      // Réinitialise le hover sur les nouvelles cartes
      initCardHover();

      // Met à jour l'URL sans recharger
      window.history.replaceState({}, '', `/shop?${params.toString()}`);

    } catch (e) {
      if (e.name !== 'AbortError') console.error('[Shop]', e);
    } finally {
      grid.classList.remove('is-loading');
    }
  }

  // ── Range slider double prix ───────────────────────────────
  const sliderMin   = document.getElementById('sliderMin');
  const sliderMax   = document.getElementById('sliderMax');
  const labelMin    = document.getElementById('priceMinLabel');
  const labelMax    = document.getElementById('priceMaxLabel');
  const rangeBar    = document.getElementById('priceSliderRange');
  const priceBtn    = document.getElementById('priceFilterBtn');

  function updateSliderUI() {
    if (!sliderMin || !sliderMax) return;
    const min = parseFloat(sliderMin.value);
    const max = parseFloat(sliderMax.value);
    const total = parseFloat(sliderMax.max);
    if (labelMin) labelMin.textContent = Math.round(min);
    if (labelMax) labelMax.textContent = Math.round(max);
    if (rangeBar) {
      rangeBar.style.left  = (min / total * 100) + '%';
      rangeBar.style.right = ((total - max) / total * 100) + '%';
    }
  }

  sliderMin?.addEventListener('input', () => {
    if (parseFloat(sliderMin.value) > parseFloat(sliderMax.value)) {
      sliderMin.value = sliderMax.value;
    }
    updateSliderUI();
  });

  sliderMax?.addEventListener('input', () => {
    if (parseFloat(sliderMax.value) < parseFloat(sliderMin.value)) {
      sliderMax.value = sliderMin.value;
    }
    updateSliderUI();
  });

  priceBtn?.addEventListener('click', applyFilters);

  updateSliderUI();

  // ── Color dots (visuel) ────────────────────────────────────
  document.querySelectorAll('.js-filter-color').forEach(cb => {
    cb.addEventListener('change', () => {
      const dot = cb.closest('label').querySelector('.nw-color-dot');
      dot?.classList.toggle('nw-color-dot--checked', cb.checked);
      applyFilters();
    });
  });

  // ── Tailles ────────────────────────────────────────────────
  document.querySelectorAll('.js-filter-size').forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  // ── Marques ────────────────────────────────────────────────
  document.querySelectorAll('.js-filter-brand').forEach(rb => {
    rb.addEventListener('change', applyFilters);
  });

  // ── Statut ─────────────────────────────────────────────────
  document.querySelectorAll('.js-filter-status').forEach(cb => {
    cb.addEventListener('change', applyFilters);
  });

  // ── Tri ────────────────────────────────────────────────────
  sortSelect?.addEventListener('change', applyFilters);

  // ── Recherche marque (live filter côté DOM) ────────────────
  document.getElementById('brandSearch')?.addEventListener('input', function () {
    const val = this.value.toLowerCase();
    document.querySelectorAll('.js-brand-item').forEach(item => {
      item.style.display = item.dataset.name.includes(val) ? '' : 'none';
    });
  });

  // ── Reset depuis le message "aucun résultat" ───────────────
  document.addEventListener('click', e => {
    if (e.target.id === 'resetFiltersBtn') {
      document.querySelectorAll('.js-filter-color, .js-filter-size, .js-filter-status').forEach(el => {
        el.checked = false;
      });
      document.querySelectorAll('.js-filter-brand').forEach(el => el.checked = false);
      document.querySelectorAll('.nw-color-dot').forEach(d => d.classList.remove('nw-color-dot--checked'));
      if (sliderMin) sliderMin.value = sliderMin.min;
      if (sliderMax) sliderMax.value = sliderMax.max;
      updateSliderUI();
      applyFilters();
    }
  });
}

/* ── Wishlist depuis la boutique ─────────────────────────── */
window.addToWishlist = async function(productId, btn) {
  try {
    const res = await fetch(`/account/wishlist/add/${productId}`, {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    });
    if (res.ok) {
      btn.style.color      = '#e53935';
      btn.style.background = '#fdecea';
    }
  } catch(e) { console.error(e); }
};

/* ══════════════════════════════════════════════════════════════════════════
   POINT D'ENTRÉE UNIQUE
   ══════════════════════════════════════════════════════════════════════════ */
function bootstrap() {
  initSingleProductPage();

  if (document.querySelector('.js-cart-increase, .js-apply-coupon, .js-cart-remove')) {
    initCartPage();
  }

  // ← Ces 3 fonctions s'exécutent sur TOUTES les pages (home incluse)
  initCardHover();
  initViewAll();
  initCategoryScroll();
  initTopSellingSlider();
  initShopFilters();
}

document.addEventListener('DOMContentLoaded', bootstrap);
document.addEventListener('turbo:load', bootstrap);