<template id="stitchDeleteTemplate">
<dialog aria-labelledby="modalTitle" aria-modal="true" class="stitch-delete-dialog fixed inset-0 z-50 flex items-center justify-center p-space-md sm:p-space-lg overflow-y-auto backdrop-blur-sm bg-inverse-surface/65" id="deleteModalBackdrop" role="dialog">
<div class="relative w-full max-w-[620px] bg-surface-container-lowest rounded-xl shadow-[0_24px_48px_rgba(15,23,42,0.22)] overflow-hidden flex flex-col my-auto transform transition-all duration-300">
<!-- Dải cảnh báo màu sắc tối cao đỉnh modal -->
<div class="h-1.5 w-full bg-gradient-to-r from-error via-tertiary to-error"></div>
<!-- HEADER MODAL -->
<div class="p-space-lg pb-space-md flex items-start justify-between gap-space-md">
<div class="flex items-start gap-space-md">
<!-- Icon Cảnh báo Nguy hiểm Cấp cao -->
<div class="w-12 h-12 rounded-xl bg-error-container text-error flex items-center justify-center flex-shrink-0 shadow-sm">
<span class="material-symbols-outlined text-[28px]" style="font-variation-settings: 'FILL' 1;">delete_forever</span>
</div>
<div class="flex flex-col">
<div class="flex items-center gap-2 mb-1">
<span class="font-label-sm text-label-sm text-error uppercase tracking-wider font-bold">
                CẢNH BÁO NGUY HIỂM TỐI CAO • KHÔNG THỂ HOÀN TÁC
              </span>
</div>
<h2 class="font-headline-md text-headline-md text-on-surface leading-snug" id="modalTitle">
              Xóa vĩnh viễn đầu sách?
            </h2>
</div>
</div>
<!-- Nút Thoát Nhanh (Esc / ✕) -->
<button aria-label="Đóng cửa sổ xác nhận" class="w-8 h-8 rounded-lg bg-surface-container-low hover:bg-surface-container-high text-on-surface-variant hover:text-on-surface flex items-center justify-center transition-colors flex-shrink-0" id="closeModalBtn" type="button">
<span class="material-symbols-outlined text-[20px]">close</span>
</button>
</div>
<!-- NỘI DUNG CHÍNH (MODAL BODY) -->
<div class="px-space-lg space-y-space-md flex-1">
<!-- KHUNG THÔNG TIN ĐẦU SÁCH SẮP XÓA (BOOK PREVIEW CARD) -->
<div class="bg-surface-container-low rounded-xl p-space-md relative overflow-hidden">
<div class="flex items-start gap-space-md">
<!-- Thumbnail Bìa Sách Thu Nhỏ -->
<div class="relative w-20 h-28 flex-shrink-0 rounded-lg overflow-hidden bg-surface-container shadow-md">
<img class="w-full h-full object-cover" data-stitch-book-image alt="">
<div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent pointer-events-none"></div>
</div>
<!-- Siêu Dữ Liệu Sách -->
<div class="flex flex-col flex-1 min-w-0">
<div class="flex items-start justify-between gap-2 mb-1">
<div>
<span class="font-label-sm text-label-sm text-secondary font-bold uppercase tracking-wide"><span data-stitch-book-code>Mã sách</span></span>
<h3 class="font-headline-sm text-headline-sm text-on-surface truncate leading-tight">
                    <span data-stitch-book-title>Tên sách</span>
                  </h3>
</div>
<span class="inline-flex items-center gap-1 font-label-sm text-label-sm bg-error-container text-on-error-container px-2 py-0.5 rounded-full flex-shrink-0 font-medium">
<span class="w-1.5 h-1.5 rounded-full bg-error animate-ping"></span>
                  Chờ xác nhận
                </span>
</div>
<p class="font-body-sm text-body-sm text-on-surface-variant">
                Tác giả: <span class="font-medium text-on-surface"><span data-stitch-book-author></span></span>
              </p>
<p class="font-label-sm text-label-sm text-outline mt-0.5 font-mono">
                <span data-stitch-book-category></span>
              </p>
<!-- Thông số lưu trữ chi tiết -->
<div class="mt-2.5 pt-2 flex flex-wrap items-center gap-x-3 gap-y-1 font-label-sm text-label-sm text-on-surface-variant bg-surface-container-lowest/80 px-2.5 py-1.5 rounded-lg">
<span class="inline-flex items-center gap-1">
<span class="material-symbols-outlined text-[15px] text-tertiary">history</span>
                  Tồn kho: <strong data-stitch-book-stock></strong>
</span>
<span class="text-outline-variant">•</span>
<span class="inline-flex items-center gap-1">
<span class="material-symbols-outlined text-[15px] text-primary">inventory_2</span>
                  Giá bán: <strong data-stitch-book-price></strong>
                </span>
</div>
<!-- Lý do gỡ bỏ trước đó -->
<div class="mt-2 text-[12px] text-on-surface-variant leading-relaxed">
<span class="font-semibold text-on-surface">Thao tác sắp thực hiện:</span> 
                Xóa đầu sách được chọn khỏi danh mục.
              </div>
</div>
</div>
</div>
<!-- HỘP CẢNH BÁO PHÁP LÝ VÀ NGHIỆP VỤ KIỂM TOÁN (DESTRUCTIVE POLICY NOTICE) -->
<div class="bg-tertiary-fixed/40 rounded-xl p-space-md text-on-tertiary-fixed-variant">
<div class="flex items-center gap-2 mb-2 font-label-md text-label-md text-tertiary font-bold">
<span class="material-symbols-outlined text-[18px]">gavel</span>
<span>LƯU Ý TRƯỚC KHI XÓA ĐẦU SÁCH</span>
</div>
<ul class="space-y-1.5 font-body-sm text-body-sm list-none">
<li class="flex items-start gap-2">
<span class="material-symbols-outlined text-error text-[16px] mt-0.5 flex-shrink-0">cancel</span>
<span>Thông tin đầu sách sẽ bị <strong>xóa khỏi danh mục</strong> sau khi yêu cầu được hệ thống xử lý thành công.</span>
</li>
<li class="flex items-start gap-2">
<span class="material-symbols-outlined text-error text-[16px] mt-0.5 flex-shrink-0">lock_reset</span>
<span><strong>Không thể khôi phục</strong> sách đã xóa bằng giao diện hiện tại.</span>
</li>
<li class="flex items-start gap-2">
<span class="material-symbols-outlined text-secondary text-[16px] mt-0.5 flex-shrink-0">account_balance</span>
<span class="text-on-surface-variant">Kiểm tra đúng mã sách, tên sách và các đơn hàng liên quan trước khi xác nhận.</span>
</li>
</ul>
</div>
<!-- CƠ CHẾ BẢO VỆ 2 LỚP (TWO-STEP SAFEGUARD VERIFICATION) -->
<div class="bg-surface-container rounded-xl p-space-md space-y-space-sm">
<div>
<label class="block font-label-md text-label-md text-on-surface mb-1" for="verificationInput">
              Vui lòng nhập chính xác mã sách <span class="font-mono text-error font-bold select-all bg-error-container/60 px-1.5 py-0.5 rounded" data-stitch-code-hint>BK-072</span> hoặc chữ <span class="font-mono text-error font-bold select-all bg-error-container/60 px-1.5 py-0.5 rounded">XÓA VĨNH VIỄN</span> để mở khóa:
            </label>
<div class="relative flex items-center">
<input class="w-full h-11 px-space-md pr-10 bg-surface-container-lowest rounded-lg font-mono text-body-md text-on-surface placeholder:text-outline-variant focus:outline-none focus:bg-surface-bright transition-all shadow-sm" id="verificationInput" placeholder="Nhập BK-072 hoặc XÓA VĨNH VIỄN..." type="text" value="" autocomplete="off" spellcheck="false">
<!-- Icon Tích Xanh Đã Khớp -->
<div class="absolute right-3 flex items-center pointer-events-none text-primary" id="verifiedBadge">
<span class="material-symbols-outlined text-[22px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
</div>
</div>
<p class="font-label-sm text-label-sm text-primary mt-1 flex items-center gap-1 font-medium" id="validationHint">
<span class="material-symbols-outlined text-[14px]">verified_user</span>
              Mã bảo vệ hợp lệ. Bước xác minh 1/2 hoàn tất.
            </p>
</div>
<!-- Checkbox Cam Kết Tổng Quản Trị -->
<label class="flex items-start gap-2.5 pt-1 cursor-pointer select-none group">
<input class="w-4 h-4 mt-0.5 rounded text-primary focus:ring-0 bg-surface-container-lowest cursor-pointer accent-primary" id="confirmCheckbox" type="checkbox">
<span class="font-label-sm text-label-sm text-on-surface group-hover:text-primary transition-colors leading-relaxed">
              Tôi là <strong>Quản trị viên</strong>, xác nhận đã kiểm tra đúng đầu sách và hiểu rằng không thể hoàn tác thao tác xóa trên giao diện.
            </span>
</label>
</div>
</div>
<!-- FOOTER THAO TÁC TƯƠNG PHẢN CAO (ACTION FOOTER) -->
<div class="p-space-lg pt-space-md flex flex-col gap-space-sm bg-surface-container-low/60 mt-space-md">
<div class="flex items-center justify-end gap-space-md">
<!-- Nút Hủy bỏ -->
<button class="px-5 py-2.5 rounded-lg bg-surface-container hover:bg-surface-container-high text-on-surface font-label-lg text-label-lg transition-all duration-150 active:scale-[0.98]" id="cancelBtn" type="button">
            Hủy bỏ (Esc)
          </button>
<!-- Nút Xóa Vĩnh Viễn Cảnh Báo Đỏ -->
<button class="px-6 py-2.5 rounded-lg bg-error text-on-error font-label-lg text-label-lg shadow-md hover:opacity-90 active:scale-[0.98] transition-all duration-150 flex items-center gap-2" id="permanentDeleteBtn" type="button">
<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1;">delete_forever</span>
<span>Xác nhận Xóa vĩnh viễn</span>
</button>
</div>
<!-- DÒNG GHI NHẬN NHẬT KÝ BẢO MẬT (AUDIT LOG TRAIL) -->
<div class="pt-2 flex items-center justify-between font-label-sm text-label-sm text-outline border-t border-surface-container-highest">
<div class="flex items-center gap-1.5">
<span class="material-symbols-outlined text-[15px] text-outline">shield</span>
<span>Người thao tác: <strong class="text-on-surface-variant font-medium"><?= htmlspecialchars($_SESSION['username'] ?? 'Quản trị viên') ?></strong></span>
</div>
<span class="font-mono text-[11px] text-outline">Xác nhận trước khi gửi yêu cầu</span>
</div>
</div>
</div>
</dialog>
</template>