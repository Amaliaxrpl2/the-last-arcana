(() => {
    'use strict';

    const storageKey = 'theLastArcana.language';
    const translations = {
        en: {
            'brand.title': 'The Last Arcana',
            'brand.tagline': 'OCCULT SURVIVAL RPG',
            'language.label': 'Language',
            'language.english': '🇬🇧 English',
            'language.indonesian': '🇮🇩 Bahasa Indonesia',
            'language.switchToId': 'Switch language to Bahasa Indonesia',
            'language.switchToEn': 'Switch language to English',
            'language.currentEn': '🇬🇧 EN',
            'language.currentId': '🇮🇩 ID',
            'settings.title': 'Settings',
            'intro.archiveRecord': 'ARCHIVE RECORD // 0001',
            'intro.headline': 'The world has forgotten your name.',
            'intro.subline': 'The Arcana has not.',
            'intro.quote': '“Not every stage was created to be cleared.”',
            'status.online': 'System online',
            'status.title': 'PLAYER STATUS',
            'status.levelShort': 'LV.',
            'status.hp': 'HP',
            'status.exp': 'EXP',
            'status.stage': 'Current stage',
            'message.label': 'SYSTEM MESSAGE',
            'message.arrival': 'The system has noticed your arrival.',
            'message.levelUp': 'LEVEL UP. Your level is now {{level}}.',
            'message.drawNew': 'A card has answered your call: {{name}}. It has been added to your collection.',
            'message.drawDuplicate': 'A card has answered your call: {{name}}. You now have {{count}} copies.',
            'message.investigate': 'You survived the investigation and gained {{amount}} EXP.',
            'message.stageCleared': 'Stage cleared. You lost {{damage}} HP and gained {{amount}} EXP.',
            'message.hpZero': 'Your HP reached zero. Rest to recover before entering another stage.',
            'message.needRest': 'Your HP is zero. Recover before trying again.',
            'message.restored': 'You recovered your HP. The next stage is waiting.',
            'draw.revealed': 'NEW CARD REVEALED',
            'rarity.common': 'Common',
            'rarity.uncommon': 'Uncommon',
            'rarity.rare': 'Rare',
            'rarity.epic': 'Epic',
            'rarity.legendary': 'Legendary',
            'rarity.mythic': 'Mythic',
            'cards.fool.description': 'A beginning with no promise of a safe ending. Its true ability remains unknown.',
            'cards.magician.description': 'A hand reaches toward the impossible. One small rule may bend, but never without a price.',
            'cards.moon.description': 'It reveals what hides in the dark. Not every revelation is the truth.',
            'cards.tower.description': 'When the old order falls, something buried beneath it begins to wake.',
            'cards.hermit.description': 'A solitary light guides the seeker, though the path remains their own.',
            'cards.high-priestess.description': 'The archive keeps its secrets behind a silent veil. Ask the wrong question, and it may answer.',
            'actions.title': 'CHOOSE YOUR ACTION',
            'actions.draw': 'Draw Arcana',
            'actions.drawHint': 'Draw a mysterious card',
            'actions.investigate': 'Investigate',
            'actions.investigateHint': 'Search for hidden clues',
            'actions.stage': 'Enter Stage',
            'actions.stageHint': 'Risk HP for more EXP',
            'actions.rest': 'Rest',
            'actions.restHint': 'Restore all HP',
            'collection.eyebrow': 'RECOVERED OBJECTS',
            'collection.title': 'Arcana Collection',
            'collection.totalCopy': '{{count}} copy',
            'collection.totalCopies': '{{count}} copies',
            'collection.uniqueCard': '{{count}} unique',
            'collection.uniqueCards': '{{count}} unique',
            'collection.empty': 'No cards recovered yet.',
            'collection.emptyHint': 'Draw an Arcana card to begin your collection. Your discoveries are saved between visits.',
            'collection.ownedCopies': 'Copies owned: {{count}}',
            'collection.copyCountSingle': '{{count}} copy',
            'collection.copyCountMany': '{{count}} copies',
            'archive.classified': 'CLASSIFIED FILE // LOCKED',
            'archive.truth': 'The truth is buried.',
            'archive.teaser': 'THE OBSERVER is watching from beyond the sealed record.',
            'archive.redacted': '████████ ███████ ███ THE OBSERVER ███████',
            'archive.previewToggle': 'Open sealed previews',
            'archive.previewNotice': 'Concept art only — these records are not playable content and have no unlock conditions yet.',
            'archive.locations': 'Sealed locations',
            'archive.entities': 'Entities under observation',
            'archive.relics': 'Uncatalogued relics',
            'archive.hunter': 'The Nameless Hunter · PREVIEW',
            'archive.observer': 'THE OBSERVER · LOCKED',
            'location.forest': 'Cursed Forest',
            'location.city': 'Abandoned City',
            'location.library': 'Ancient Library',
            'location.ruins': 'Underground Ruins',
            'location.sanctuary': 'Occult Sanctuary',
            'item.relic': 'Ashen Relic',
            'item.blade': 'Moonsteel Blade',
            'item.mirror': 'Veiled Mirror',
            'footer.version': 'ARCHIVE VERSION 0.1',
            'footer.reset': 'Reset player status',
            'confirm.reset': 'Reset player status? Your Arcana Collection will be kept.',
            'alt.playerPortrait': 'Portrait of The Unregistered',
            'alt.observer': 'The Observer, a concealed figure within the forbidden archive',
            'alt.hunter': 'The Nameless Hunter, sealed entity concept art',
            'alt.tarotIllustration': '{{name}} — original tarot illustration',
            'alt.location.forest': 'Cursed Forest concept art',
            'alt.location.city': 'Abandoned City concept art',
            'alt.location.library': 'Ancient Library concept art',
            'alt.location.ruins': 'Underground Ruins concept art',
            'alt.location.sanctuary': 'Occult Sanctuary concept art',
            'alt.item.relic': 'Ashen Relic concept art',
            'alt.item.blade': 'Moonsteel Blade concept art',
            'alt.item.mirror': 'Veiled Mirror concept art'
        },
        id: {
            'brand.title': 'The Last Arcana',
            'brand.tagline': 'RPG BERTAHAN HIDUP OKULTIS',
            'language.label': 'Bahasa',
            'language.english': '🇬🇧 English',
            'language.indonesian': '🇮🇩 Bahasa Indonesia',
            'language.switchToId': 'Ganti bahasa ke Bahasa Indonesia',
            'language.switchToEn': 'Ganti bahasa ke English',
            'language.currentEn': '🇬🇧 EN',
            'language.currentId': '🇮🇩 ID',
            'settings.title': 'Pengaturan',
            'intro.archiveRecord': 'CATATAN ARSIP // 0001',
            'intro.headline': 'Dunia telah melupakan namamu.',
            'intro.subline': 'Arcana belum.',
            'intro.quote': '“Tidak semua tahap diciptakan untuk dituntaskan.”',
            'status.online': 'Sistem aktif',
            'status.title': 'STATUS PEMAIN',
            'status.levelShort': 'LV.',
            'status.hp': 'HP',
            'status.exp': 'EXP',
            'status.stage': 'Tahap saat ini',
            'message.label': 'PESAN SISTEM',
            'message.arrival': 'Sistem menyadari kedatanganmu.',
            'message.levelUp': 'NAIK LEVEL. Levelmu kini {{level}}.',
            'message.drawNew': 'Sebuah kartu menjawab panggilanmu: {{name}}. Kartu ini telah ditambahkan ke koleksimu.',
            'message.drawDuplicate': 'Sebuah kartu menjawab panggilanmu: {{name}}. Kini kamu memiliki {{count}} salinan.',
            'message.investigate': 'Kamu selamat dari penyelidikan dan memperoleh {{amount}} EXP.',
            'message.stageCleared': 'Tahap terlewati. Kamu kehilangan {{damage}} HP dan memperoleh {{amount}} EXP.',
            'message.hpZero': 'HP-mu mencapai nol. Beristirahatlah untuk pulih sebelum memasuki tahap berikutnya.',
            'message.needRest': 'HP-mu nol. Pulihkan diri sebelum mencoba lagi.',
            'message.restored': 'HP-mu telah pulih. Tahap berikutnya menantimu.',
            'draw.revealed': 'KARTU BARU TERUNGKAP',
            'rarity.common': 'Umum',
            'rarity.uncommon': 'Tidak Umum',
            'rarity.rare': 'Langka',
            'rarity.epic': 'Epik',
            'rarity.legendary': 'Legendaris',
            'rarity.mythic': 'Mitis',
            'cards.fool.description': 'Awal tanpa janji akhir yang aman. Kemampuan sejatinya masih menjadi misteri.',
            'cards.magician.description': 'Satu tangan meraih hal yang mustahil. Sebuah aturan kecil dapat dibengkokkan, tetapi selalu ada harganya.',
            'cards.moon.description': 'Ia menyingkap yang bersembunyi dalam kegelapan. Tidak semua yang terungkap adalah kebenaran.',
            'cards.tower.description': 'Saat tatanan lama runtuh, sesuatu yang terkubur di bawahnya mulai terbangun.',
            'cards.hermit.description': 'Cahaya sunyi menuntun sang pencari, tetapi jalan itu tetap harus ditempuh sendiri.',
            'cards.high-priestess.description': 'Arsip menyimpan rahasianya di balik tabir bisu. Ajukan pertanyaan yang salah, dan ia mungkin menjawab.',
            'actions.title': 'PILIH TINDAKAN',
            'actions.draw': 'Tarik Arcana',
            'actions.drawHint': 'Tarik kartu misterius',
            'actions.investigate': 'Selidiki',
            'actions.investigateHint': 'Cari petunjuk tersembunyi',
            'actions.stage': 'Masuki Tahap',
            'actions.stageHint': 'Pertaruhkan HP demi EXP',
            'actions.rest': 'Istirahat',
            'actions.restHint': 'Pulihkan seluruh HP',
            'collection.eyebrow': 'BENDA YANG DITEMUKAN',
            'collection.title': 'Koleksi Arcana',
            'collection.totalCopy': '{{count}} kartu',
            'collection.totalCopies': '{{count}} kartu',
            'collection.uniqueCard': '{{count}} kartu unik',
            'collection.uniqueCards': '{{count}} kartu unik',
            'collection.empty': 'Belum ada kartu yang ditemukan.',
            'collection.emptyHint': 'Tarik kartu Arcana untuk memulai koleksimu. Semua temuan disimpan untuk kunjungan berikutnya.',
            'collection.ownedCopies': 'Kartu dimiliki: {{count}}',
            'collection.copyCountSingle': '{{count}} salinan',
            'collection.copyCountMany': '{{count}} salinan',
            'archive.classified': 'BERKAS RAHASIA // TERKUNCI',
            'archive.truth': 'Kebenaran terkubur.',
            'archive.teaser': 'THE OBSERVER mengawasi dari balik berkas yang tersegel.',
            'archive.redacted': '████████ ███████ ███ THE OBSERVER ███████',
            'archive.previewToggle': 'Buka pratinjau tersegel',
            'archive.previewNotice': 'Hanya seni konsep — berkas ini bukan konten yang dapat dimainkan dan belum memiliki syarat pembukaan.',
            'archive.locations': 'Lokasi tersegel',
            'archive.entities': 'Entitas dalam pengawasan',
            'archive.relics': 'Relik yang belum terdata',
            'archive.hunter': 'Pemburu Tanpa Nama · PRATINJAU',
            'archive.observer': 'THE OBSERVER · TERKUNCI',
            'location.forest': 'Hutan Terkutuk',
            'location.city': 'Kota Terbengkalai',
            'location.library': 'Perpustakaan Kuno',
            'location.ruins': 'Reruntuhan Bawah Tanah',
            'location.sanctuary': 'Tempat Suci Okultis',
            'item.relic': 'Relik Abu',
            'item.blade': 'Pedang Baja Rembulan',
            'item.mirror': 'Cermin Bertudung',
            'footer.version': 'ARSIP VERSI 0.1',
            'footer.reset': 'Atur ulang status pemain',
            'confirm.reset': 'Atur ulang status pemain? Koleksi Arcana akan tetap tersimpan.',
            'alt.playerPortrait': 'Potret The Unregistered',
            'alt.observer': 'The Observer, sosok tersembunyi di dalam arsip terlarang',
            'alt.hunter': 'Pemburu Tanpa Nama, seni konsep entitas tersegel',
            'alt.tarotIllustration': '{{name}} — ilustrasi tarot orisinal',
            'alt.location.forest': 'Seni konsep Hutan Terkutuk',
            'alt.location.city': 'Seni konsep Kota Terbengkalai',
            'alt.location.library': 'Seni konsep Perpustakaan Kuno',
            'alt.location.ruins': 'Seni konsep Reruntuhan Bawah Tanah',
            'alt.location.sanctuary': 'Seni konsep Tempat Suci Okultis',
            'alt.item.relic': 'Seni konsep Relik Abu',
            'alt.item.blade': 'Seni konsep Pedang Baja Rembulan',
            'alt.item.mirror': 'Seni konsep Cermin Bertudung'
        }
    };

    const interpolate = (text, params) => text.replace(/\{\{(\w+)\}\}/g, (_, key) => (
        Object.prototype.hasOwnProperty.call(params, key) ? String(params[key]) : ''
    ));

    const getSavedLanguage = () => {
        try {
            const saved = window.localStorage.getItem(storageKey);
            return saved === 'id' || saved === 'en' ? saved : 'en';
        } catch (_error) {
            return 'en';
        }
    };

    let currentLanguage = getSavedLanguage();

    const translate = (key, params = {}) => {
        const value = translations[currentLanguage][key] ?? translations.en[key] ?? key;
        return interpolate(value, params);
    };

    const readParams = (element, attribute) => {
        try {
            return JSON.parse(element.getAttribute(attribute) || '{}');
        } catch (_error) {
            return {};
        }
    };

    const applyTranslations = () => {
        const language = translations[currentLanguage];
        document.documentElement.lang = currentLanguage;

        document.querySelectorAll('[data-i18n]').forEach((element) => {
            element.textContent = translate(element.dataset.i18n, readParams(element, 'data-i18n-params'));
        });
        document.querySelectorAll('[data-i18n-alt]').forEach((element) => {
            element.alt = translate(element.dataset.i18nAlt, readParams(element, 'data-i18n-alt-params'));
        });
        document.querySelectorAll('[data-i18n-title]').forEach((element) => {
            element.title = translate(element.dataset.i18nTitle);
        });
        document.querySelectorAll('[data-i18n-aria-label]').forEach((element) => {
            element.setAttribute('aria-label', translate(element.dataset.i18nAriaLabel));
        });

        const selector = document.getElementById('language-select');
        if (selector) selector.value = currentLanguage;

        const toggle = document.getElementById('language-toggle');
        if (toggle) {
            toggle.textContent = language[currentLanguage === 'en' ? 'language.currentEn' : 'language.currentId'];
            toggle.setAttribute('aria-label', translate(currentLanguage === 'en' ? 'language.switchToId' : 'language.switchToEn'));
        }
    };

    const setLanguage = (language) => {
        if (language !== 'en' && language !== 'id') return;
        currentLanguage = language;
        try {
            window.localStorage.setItem(storageKey, language);
        } catch (_error) {
            // The language still changes for this page even when storage is unavailable.
        }
        applyTranslations();
    };

    document.getElementById('language-toggle')?.addEventListener('click', () => {
        setLanguage(currentLanguage === 'en' ? 'id' : 'en');
    });
    document.getElementById('language-select')?.addEventListener('change', (event) => {
        setLanguage(event.currentTarget.value);
    });
    document.querySelectorAll('form[data-confirm-i18n]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm(translate(form.dataset.confirmI18n))) event.preventDefault();
        });
    });

    applyTranslations();
})();
