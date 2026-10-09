{{-- Modal pratinjau PDF (tanpa download). Tombol pembuka: data-bs-toggle="modal" data-bs-target="#previewModal" data-preview-url="..." --}}
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pratinjau Kontrol Server &amp; Jaringan</h5>
                <a href="#" target="_blank" id="previewOpenTab" class="btn btn-sm btn-outline-secondary ms-auto me-3">
                    <i class="ti tabler-external-link me-1"></i> Buka di tab baru
                </a>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body p-0 position-relative">
                <div id="previewLoading" class="position-absolute top-50 start-50 translate-middle text-center text-body-secondary">
                    <div class="spinner-border mb-2" role="status"></div>
                    <div>Menyiapkan form… (bisa beberapa detik)</div>
                </div>
                <iframe id="previewFrame" title="Pratinjau form" style="width:100%; height:100%; border:0; position:relative;"></iframe>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var modal = document.getElementById('previewModal');
    var frame = document.getElementById('previewFrame');
    var loading = document.getElementById('previewLoading');
    var openTab = document.getElementById('previewOpenTab');
    if (!modal || !frame) return;

    // Dimuat ulang tiap dibuka, supaya selalu menampilkan data terbaru.
    modal.addEventListener('show.bs.modal', function (e) {
        var url = e.relatedTarget ? e.relatedTarget.getAttribute('data-preview-url') : null;
        if (!url) return;
        loading.style.display = '';
        openTab.setAttribute('href', url);
        frame.onload = function () { loading.style.display = 'none'; };
        frame.src = url;
    });
    modal.addEventListener('hidden.bs.modal', function () {
        frame.onload = null;
        frame.src = 'about:blank';
    });
})();
</script>
@endpush
