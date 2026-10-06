import Alpine from 'alpinejs';
import { createIcons, icons } from 'lucide';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

window.downloadAppointmentProof = function(button) {
    const data = button.dataset;
    const canvas = document.createElement('canvas');
    const context = canvas.getContext('2d');
    const lines = [
        ['Appointment proof', 'Purita\'s Beauty Lounge'],
        ['Code', data.proofCode],
        ['Status', data.proofStatus],
        ['Customer', data.proofName],
        ['Contact number', data.proofPhone],
        ['Address', data.proofAddress],
        ['Date', data.proofDate],
        ['Time', data.proofTime],
        ['Staff / stylist', data.proofStaff],
        ['Services', data.proofServices],
        ['Total', data.proofTotal],
    ];
    const width = 1100;
    const lineHeight = 54;
    const padding = 70;
    canvas.width = width;
    canvas.height = padding * 2 + lineHeight * (lines.length + 1);

    context.fillStyle = '#fffdf7';
    context.fillRect(0, 0, canvas.width, canvas.height);
    context.fillStyle = '#7A1C49';
    context.fillRect(0, 0, canvas.width, 18);
    context.font = '700 34px sans-serif';
    context.fillText(lines[0][0], padding, padding + 10);
    context.font = '600 22px sans-serif';
    context.fillStyle = '#5c0e2a';
    context.fillText(lines[0][1], width - 360, padding + 10);

    lines.slice(1).forEach(([label, value], index) => {
        const y = padding + lineHeight * (index + 2);
        context.font = '700 20px sans-serif';
        context.fillStyle = '#6b7280';
        context.fillText(`${label}:`, padding, y);
        context.font = '600 22px sans-serif';
        context.fillStyle = '#111827';
        context.fillText(value, padding + 220, y);
    });

    const link = document.createElement('a');
    link.download = `${data.proofCode}-appointment-proof.png`;
    link.href = canvas.toDataURL('image/png');
    link.click();
};

// Global Senior Text Scaling Helper
window.setFontScale = function(scale) {
    document.documentElement.style.setProperty('--font-scale', scale);
    localStorage.setItem('salon_font_scale', scale);
};

// Restore persisted scale
const savedScale = localStorage.getItem('salon_font_scale');
if (savedScale) {
    document.documentElement.style.setProperty('--font-scale', savedScale);
}

Alpine.start();

if ('scrollRestoration' in window.history) {
    window.history.scrollRestoration = 'manual';
}

const sidebarScrollSelector = '[data-sidebar-scroll]';
const pageScrollKey = 'salon_page_scroll_y';

const persistScrollState = () => {
    sessionStorage.setItem(pageScrollKey, String(window.scrollY || document.documentElement.scrollTop || 0));

    document.querySelectorAll(sidebarScrollSelector).forEach((sidebar) => {
        const key = `salon_sidebar_scroll_${sidebar.dataset.sidebarScroll}`;
        sessionStorage.setItem(key, String(sidebar.scrollTop || 0));
    });
};

const restoreScrollState = () => {
    const savedPageScroll = Number(sessionStorage.getItem(pageScrollKey) || 0);
    if (Number.isFinite(savedPageScroll) && savedPageScroll > 0) {
        window.scrollTo({ top: savedPageScroll, left: 0, behavior: 'auto' });
    }

    document.querySelectorAll(sidebarScrollSelector).forEach((sidebar) => {
        const key = `salon_sidebar_scroll_${sidebar.dataset.sidebarScroll}`;
        const savedScrollTop = Number(sessionStorage.getItem(key) || 0);

        if (Number.isFinite(savedScrollTop)) {
            sidebar.scrollTop = savedScrollTop;
        }
    });
};

document.addEventListener('click', (event) => {
    const anchor = event.target.closest('a[href]');
    if (!anchor) return;

    const isSameOrigin = !anchor.target || anchor.target === '_self';
    const isInternalLink = anchor.href && anchor.href.startsWith(window.location.origin);

    if (isSameOrigin && isInternalLink) {
        persistScrollState();
    }
});

window.addEventListener('pagehide', persistScrollState);
window.addEventListener('beforeunload', persistScrollState);

document.addEventListener('DOMContentLoaded', () => {
    restoreScrollState();
    createIcons({ icons });
});
