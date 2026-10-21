(() => {
    'use strict';
    let activeDialog = null;
    let pending = null;
    let previousFocus = null;
    const confirmBook = (book) => {
        if (pending) return Promise.resolve(false);
        const template = document.getElementById('stitchDeleteTemplate');
        if (!template) return Promise.resolve(window.confirm(`Xóa sách "${book.title}"?`));
        const dialog = template.content.firstElementChild.cloneNode(true);
        activeDialog = dialog;
        const code = `BK-${String(book.id).padStart(3, '0')}`;
        dialog.dataset.code = code;
        const fill = (selector, value) => {
            dialog.querySelector(selector).textContent = value;
        };
        fill('[data-stitch-book-code]', `Mã: #${code}`);
        fill('[data-stitch-code-hint]', code);
        fill('[data-stitch-book-title]', book.title || `Sách #${book.id}`);
        fill('[data-stitch-book-author]', book.author || 'Chưa cập nhật');
        fill('[data-stitch-book-category]', `Thể loại: ${book.category_name || book.category || 'Chưa cập nhật'}`);
        fill('[data-stitch-book-stock]', book.quantity == null ? 'Chưa cập nhật' : `${book.quantity} cuốn`);
        fill('[data-stitch-book-price]', book.price == null ? 'Chưa cập nhật' : `${Number(book.price).toLocaleString('vi-VN')} VNĐ`);
        const image = dialog.querySelector('[data-stitch-book-image]');
        if (book.imageUrl) image.src = book.imageUrl;
        else image.hidden = true;
        image.onerror = () => { image.hidden = true; };
        const input = dialog.querySelector('#verificationInput');
        const checkbox = dialog.querySelector('#confirmCheckbox');
        const deleteButton = dialog.querySelector('#permanentDeleteBtn');
        input.value = '';
        input.placeholder = `Nhập ${code} hoặc XÓA VĨNH VIỄN...`;
        checkbox.checked = false;
        const checkEligibility = () => {
            const value = input.value.trim().toUpperCase();
            const valid = value === code || value === 'XÓA VĨNH VIỄN' || value === 'XOA VINH VIEN';
            dialog.querySelector('#verifiedBadge').hidden = !valid;
            const hint = dialog.querySelector('#validationHint');
            hint.classList.toggle('text-error', !valid);
            hint.classList.toggle('text-primary', valid);
            hint.textContent = valid ? 'Mã bảo vệ hợp lệ. Bước xác minh 1/2 hoàn tất.' : `Vui lòng nhập đúng ${code} hoặc XÓA VĨNH VIỄN.`;
            deleteButton.disabled = !(valid && checkbox.checked);
            deleteButton.classList.toggle('opacity-40', deleteButton.disabled);
            deleteButton.classList.toggle('cursor-not-allowed', deleteButton.disabled);
        };
        input.addEventListener('input', checkEligibility);
        checkbox.addEventListener('change', checkEligibility);
        dialog.querySelector('#cancelBtn').addEventListener('click', () => finish(false));
        dialog.querySelector('#closeModalBtn').addEventListener('click', () => finish(false));
        dialog.addEventListener('cancel', (event) => { event.preventDefault(); finish(false); });
        dialog.addEventListener('close', () => { if (activeDialog === dialog && pending) finish(false); });
        deleteButton.addEventListener('click', () => { if (!deleteButton.disabled) finish(true); });
        checkEligibility();
        previousFocus = document.activeElement;
        document.body.classList.add('ui-dialog-open');
        document.body.append(dialog);
        return new Promise((resolve) => {
            pending = resolve;
            dialog.showModal();
            input.focus();
        });
    };
    function finish(confirmed) {
        const resolve = pending;
        const dialog = activeDialog;
        pending = null;
        activeDialog = null;
        dialog?.close();
        dialog?.remove();
        document.body.classList.remove('ui-dialog-open');
        if (previousFocus?.isConnected) previousFocus.focus();
        if (resolve) resolve(confirmed);
    }
    window.BookstoreUI = { confirmBook };
    document.addEventListener('DOMContentLoaded', () => {
        const toggle = document.querySelector('.stitch-menu-toggle');
        const closeMenu = () => {
            document.body.classList.remove('stitch-nav-open');
            toggle?.setAttribute('aria-expanded', 'false');
        };
        toggle?.addEventListener('click', () => {
            const open = document.body.classList.toggle('stitch-nav-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
        document.addEventListener('keydown', (event) => { if (event.key === 'Escape') closeMenu(); });
        document.addEventListener('click', (event) => {
            if (document.body.classList.contains('stitch-nav-open') && !event.target.closest('.stitch-sidebar,.stitch-menu-toggle')) closeMenu();
        });
        document.querySelectorAll('a[href*="delete_book.php"]').forEach((link) => {
            link.removeAttribute('onclick');
            link.addEventListener('click', async (event) => {
                event.preventDefault();
                const url = new URL(link.href);
                const row = link.closest('tr');
                const detailRows = [...document.querySelectorAll('table tr')];
                const detail = (label) => detailRows.find((r) => r.querySelector('th')?.textContent.includes(label))?.querySelector('td')?.textContent.trim();
                const title = row?.cells[1]?.textContent.trim() || detail('Tên sản phẩm');
                const priceText = row?.cells[3]?.textContent || detail('Giá');
                const confirmed = await confirmBook({
                    id: url.searchParams.get('id'), title,
                    author: row?.cells[2]?.textContent.trim() || detail('Tác giả'),
                    category: row?.cells[4]?.textContent.trim() || detail('Thể loại'),
                    price: priceText ? Number(priceText.replace(/[^0-9]/g, '')) : undefined,
                    imageUrl: row?.querySelector('img')?.src || document.querySelector('table img')?.src
                });
                if (confirmed) window.location.assign(url.href);
            });
        });
    });
})();
