const API_BASE = 'https://api.jikan.moe/v4';

const els = {
    form: document.getElementById('searchForm'),
    q: document.getElementById('q'),
    type: document.getElementById('type'),
    status: document.getElementById('status'),
    year: document.getElementById('year'),
    orderBy: document.getElementById('orderBy'),
    sort: document.getElementById('sort'),
    results: document.getElementById('results'),
    statusText: document.getElementById('status'),
    prevBtn: document.getElementById('prevBtn'),
    nextBtn: document.getElementById('nextBtn'),
    pageLabel: document.getElementById('pageLabel'),
    modal: document.getElementById('detailModal'),
    detail: document.getElementById('detailContent')
};

let state = {
    page: 1,
    hasNext: false,
    lastQuery: null,
    controller: null
};

function buildQuery(page = 1) {
    const params = new URLSearchParams();
    const q = els.q.value.trim();
    if (q) params.set('q', q);
    const type = els.type.value;
    if (type) params.set('type', type);
    const status = els.status.value;
    if (status) params.set('status', status);
    const year = els.year.value.trim();
    if (year) params.set('start_date', `${year}-01-01`);
    const orderBy = els.orderBy.value || 'score';
    params.set('order_by', orderBy);
    params.set('sort', els.sort.value || 'desc');
    params.set('sfw', 'true'); // เนื้อหาเหมาะสม
    params.set('limit', '18');
    params.set('page', String(page));
    return params;
}

function setLoading(msg = 'กำลังดึงข้อมูล…') {
    els.statusText.textContent = msg;
}

function setError(msg = 'เกิดข้อผิดพลาดในการเชื่อมต่อ API') {
    els.statusText.textContent = msg;
}

function setPager(page, hasNext) {
    els.pageLabel.textContent = `หน้า ${page}`;
    els.prevBtn.disabled = page <= 1;
    els.nextBtn.disabled = !hasNext;
}

function renderResults(list = []) {
    els.results.innerHTML = '';
    list.forEach(item => {
        const img = item.images?.jpg?.image_url || item.images?.webp?.image_url || '';
        const title = item.title || item.title_english || item.title_japanese || 'Untitled';
        const score = item.score ?? '–';
        const type = item.type || '—';
        const year = item.year || (item.aired?.prop?.from?.year || '—');
        const episodes = item.episodes ?? '—';
        const status = item.status || '—';

        const card = document.createElement('div');
        card.className = 'card';
        card.innerHTML = `
        <img loading="lazy" src="${img}" alt="${title}">
        <div class="body">
            <div class="title">${title}</div>
            <div class="badges">
            <span class="badge">${type}</span>
            <span class="badge">⭐ ${score}</span>
            <span class="badge">EP ${episodes}</span>
            <span class="badge">ปี ${year}</span>
        </div>
        <div class="meta">${status}</div>
            <div class="actions">
                <button class="primary" data-id="${item.mal_id}">รายละเอียด</button>
                <a href="${item.url}" target="_blank" rel="noreferrer"><button>ดูบน MyAnimeList</button></a>
            </div>
        </div>
        `;
        els.results.appendChild(card);
    });
}

async function search(page = 1) {
  // ยกเลิกคำขอเก่า (กันคีย์แล้วสแปม)
    if (state.controller) state.controller.abort();
    state.controller = new AbortController();

    const params = buildQuery(page);
    const url = `${API_BASE}/anime?${params.toString()}`;

    setLoading('กำลังดึงข้อมูล…');
    try {
        const res = await fetch(url, { signal: state.controller.signal });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();

        const list = json.data || [];
        const hasNext = json.pagination?.has_next_page || false;

        renderResults(list);
        setPager(page, hasNext);
        state.page = page;
        state.hasNext = hasNext;
        state.lastQuery = params.toString();

        if (list.length === 0) {
        els.statusText.textContent = 'ไม่พบผลลัพธ์ ลองเปลี่ยนคำค้นหาหรือฟิลเตอร์';
        } else {
        els.statusText.textContent = `พบ ${json.pagination?.items?.total ?? list.length} รายการ (อาจแสดงบางส่วน)`;
        }
    } catch (e) {
        if (e.name === 'AbortError') return;
        console.error(e);
        setError('เชื่อมต่อ API ไม่สำเร็จ หรือถูกจำกัดอัตรา (ลองใหม่อีกครั้ง)');
    }
}

async function openDetail(malId) {
    try {
        els.detail.innerHTML = 'กำลังโหลดรายละเอียด…';
        els.modal.showModal();

        // endpoint รายละเอียดเต็ม
        const res = await fetch(`${API_BASE}/anime/${malId}/full`);
        if (!res.ok) throw new Error('http ' + res.status);
        const { data } = await res.json();

        const img = data.images?.jpg?.large_image_url || data.images?.webp?.large_image_url || '';
        const title = data.title || data.title_english || data.title_japanese || 'Untitled';
        const studios = (data.studios || []).map(s => s.name).join(', ') || '—';
        const genres = (data.genres || []).map(g => g.name).join(', ') || '—';
        const themes = (data.themes || []).map(t => t.name).join(', ');
        const score = data.score ?? '—';
        const rank = data.rank ?? '—';
        const popularity = data.popularity ?? '—';
        const episodes = data.episodes ?? '—';
        const duration = data.duration || '—';
        const status = data.status || '—';
        const year = data.year || (data.aired?.prop?.from?.year || '—');
        const trailer = data.trailer?.url;

        els.detail.innerHTML = `
        <div class="detail-grid">
            <div><img src="${img}" alt="${title}"></div>
            <div>
            <h2>${title}</h2>
            <div class="badges" style="margin:8px 0 12px;">
                <span class="badge">⭐ ${score}</span>
                <span class="badge">อันดับ #${rank}</span>
                <span class="badge">นิยม #${popularity}</span>
            </div>

            <div class="kv">
                <div>สตูดิโอ</div><div>${studios}</div>
                <div>ประเภท</div><div>${data.type || '—'}</div>
                <div>แนว/ธีม</div><div>${[genres, themes].filter(Boolean).join(' · ') || '—'}</div>
                <div>ตอน</div><div>${episodes}</div>
                <div>เวลาต่อตอน</div><div>${duration}</div>
                <div>ปี</div><div>${year}</div>
                <div>สถานะ</div><div>${status}</div>
            </div>

            <div class="synopsis"><strong>เรื่องย่อ:</strong>
            ${data.synopsis ? data.synopsis : '— ไม่มีข้อมูล —'}</div>

            <div class="actions" style="margin-top:12px;">
                ${trailer ? `<a href="${trailer}" target="_blank" rel="noreferrer"><button class="primary">ดูเทรลเลอร์</button></a>` : ''}
                <a href="${data.url}" target="_blank" rel="noreferrer"><button>ดูบน MAL</button></a>
            </div>
            </div>
        </div>
        `;
    } catch (e) {
        console.error(e);
        els.detail.innerHTML = 'โหลดรายละเอียดไม่สำเร็จ ลองใหม่อีกครั้ง';
    }
}

// events
els.form.addEventListener('submit', (e) => {
    e.preventDefault();
    search(1);
});

els.prevBtn.addEventListener('click', () => search(state.page - 1));
els.nextBtn.addEventListener('click', () => search(state.page + 1));

els.results.addEventListener('click', (e) => {
    const btn = e.target.closest('button.primary');
    if (btn?.dataset?.id) openDetail(btn.dataset.id);
});

// เดโม่: โหลด “กำลังฉาย” เริ่มต้น
els.status.value = 'airing';
search(1);
