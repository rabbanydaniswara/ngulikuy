 </main>

    <!-- ACTION MODAL -->
    <div id="actionModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div id="modal-overlay" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div id="modal-content" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div id="modal-icon-container" class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i id="modal-icon" data-feather="info" class="h-6 w-6 text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Konfirmasi Tindakan</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500" id="modal-description">Apakah Anda yakin?</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" id="modal-confirm-btn" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">
                        <span class="btn-text">Konfirmasi</span>
                        <span class="btn-loading hidden items-center">
                            <i data-feather="loader" class="animate-spin -ml-1 mr-2 h-5 w-5"></i>
                            Memproses...
                        </span>
                    </button>
                    <button type="button" id="modal-cancel-btn" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script>
        // Render feather icons
        feather.replace();

        // CSRF Token
        const CSRF_TOKEN = '<?php echo getCsrfToken(); ?>';

        // Notifikasi AJAX
        const ajaxNotification = document.getElementById('ajax-notification');
        function showAjaxNotification(message, type = 'success') {
            ajaxNotification.textContent = message;
            ajaxNotification.className = '';
            ajaxNotification.classList.add(type === 'success' ? 'success' : 'error');
            ajaxNotification.style.display = 'block';
            ajaxNotification.style.opacity = 1;
            setTimeout(() => {
                ajaxNotification.style.opacity = 0;
                setTimeout(() => { ajaxNotification.style.display = 'none'; }, 500);
            }, 3000);
        }

        // Modal elements
        const actionModal = document.getElementById('actionModal');
        const modalOverlay = document.getElementById('modal-overlay');
        const modalContent = document.getElementById('modal-content');
        const modalTitle = document.getElementById('modal-title');
        const modalDescription = document.getElementById('modal-description');
        const modalConfirmBtn = document.getElementById('modal-confirm-btn');
        const modalCancelBtn = document.getElementById('modal-cancel-btn');
        const modalIcon = document.getElementById('modal-icon');
        const modalIconContainer = document.getElementById('modal-icon-container');

        function closeModal() {
            actionModal.classList.add('hidden');
        }

        // Attach modal listeners to buttons with .job-modal-trigger
        function attachModalListeners() {
            document.querySelectorAll('.job-modal-trigger').forEach(button => {
                // remove old listener (safe)
                button.removeEventListener('click', openModalHandler);
                button.addEventListener('click', openModalHandler);
            });
        }

        function openModalHandler() {
            const action = this.dataset.action;
            const jobId = this.dataset.jobId;
            const jobType = this.dataset.jobType;
            const jobCustomer = this.dataset.jobCustomer;
            const jobTitle = this.dataset.jobTitle;

            let title = 'Konfirmasi Tindakan';
            let description = 'Apakah Anda yakin?';
            let confirmText = 'Konfirmasi';
            let confirmClass = 'bg-blue-600 hover:bg-blue-700';
            let iconName = 'info';
            let iconClass = 'text-blue-600';
            let iconContainerClass = 'bg-blue-100';

            if (action === 'worker_accept_job') {
                title = 'Terima Pekerjaan?';
                description = `Anda akan menerima pekerjaan <strong>${jobType}</strong> dari customer <strong>${jobCustomer}</strong>. Lanjutkan?`;
                confirmText = 'Ya, Terima';
                confirmClass = 'bg-green-600 hover:bg-green-700';
                iconName = 'check-circle';
                iconClass = 'text-green-600';
                iconContainerClass = 'bg-green-100';
            } else if (action === 'worker_reject_job') {
                title = 'Tolak Pekerjaan?';
                description = `Anda akan menolak pekerjaan <strong>${jobType}</strong> dari customer <strong>${jobCustomer}</strong>. Tindakan ini tidak dapat dibatalkan.`;
                confirmText = 'Ya, Tolak';
                confirmClass = 'bg-red-600 hover:bg-red-700';
                iconName = 'x-circle';
                iconClass = 'text-red-600';
                iconContainerClass = 'bg-red-100';
            } else if (action === 'worker_complete_job') {
                title = 'Selesaikan Pekerjaan?';
                description = `Konfirmasi bahwa pekerjaan <strong>${jobType}</strong> untuk <strong>${jobCustomer}</strong> telah selesai.`;
                confirmText = 'Ya, Selesaikan';
                confirmClass = 'bg-blue-600 hover:bg-blue-700';
                iconName = 'check-square';
                iconClass = 'text-blue-600';
                iconContainerClass = 'bg-blue-100';
            } else if (action === 'worker_take_posted_job') {
                title = 'Ambil Pekerjaan Ini?';
                description = `Anda akan mengambil pekerjaan <strong>"${jobTitle}"</strong>. Customer akan diinformasikan. Lanjutkan?`;
                confirmText = 'Ya, Ambil Pekerjaan';
                confirmClass = 'bg-green-600 hover:bg-green-700';
                iconName = 'plus-circle';
                iconClass = 'text-green-600';
                iconContainerClass = 'bg-green-100';
            }


            // Populate modal
            modalTitle.textContent = title;
            modalDescription.innerHTML = description;

            const btnText = modalConfirmBtn.querySelector('.btn-text');
            if (btnText) btnText.textContent = confirmText;

            // replace bg classes (simple way: remove known patterns)
            modalConfirmBtn.className = modalConfirmBtn.className.replace(/\bbg-\S+\b/g, '').replace(/\bhover:bg-\S+\b/g, '');
            modalConfirmBtn.classList.add(...confirmClass.split(' '));

            // icon
            modalIcon.setAttribute('data-feather', iconName);
            modalIcon.className = `h-6 w-6 ${iconClass}`;
            modalIconContainer.className = `mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ${iconContainerClass}`;
            feather.replace();

            modalConfirmBtn.dataset.action = action;
            modalConfirmBtn.dataset.jobId = jobId;

            actionModal.classList.remove('hidden');
        }

        attachModalListeners();

        modalCancelBtn.addEventListener('click', closeModal);
        modalOverlay.addEventListener('click', closeModal);

        modalConfirmBtn.addEventListener('click', async function() {
            const action = this.dataset.action;
            const jobId = this.dataset.jobId;
            const row = document.getElementById('job-row-' + jobId);

            const btnText = this.querySelector('.btn-text');
            const btnLoading = this.querySelector('.btn-loading');

            // Show loading
            if (btnText) btnText.classList.add('hidden');
            if (btnLoading) { btnLoading.classList.remove('hidden'); btnLoading.classList.add('flex'); }
            this.disabled = true;
            modalCancelBtn.disabled = true;

            const formData = new FormData();
            formData.append('action', action);
            formData.append('job_id', jobId);
            formData.append('csrf_token', CSRF_TOKEN);

            try {
                const response = await fetch('ajax_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();

                closeModal();

                if (data.success) {
                    showAjaxNotification(data.message, 'success');

                     // Generic row removal for actions that move items between tabs
                    if (row && (action === 'worker_accept_job' || action === 'worker_reject_job' || action === 'worker_complete_job' || action === 'worker_take_posted_job')) {
                        row.style.transition = 'opacity 0.5s ease';
                        row.style.opacity = '0';
                        setTimeout(() => {
                            row.remove();
                            // Check if the list is now empty
                            const container = document.querySelector('.space-y-4');
                            if (container && container.children.length === 0) {
                                const activeTab = new URLSearchParams(window.location.search).get('tab') || 'pending';
                                if(activeTab !== 'find_jobs') {
                                    document.querySelector('.overflow-x-auto').insertAdjacentHTML('afterend', '<div class="px-4 py-4 text-center text-gray-500">Tidak ada pekerjaan di kategori ini.</div>');
                                }
                            }
                        }, 500);
                    } else if (row) { // Fallback for old logic if needed, though above is better
                        const statusBadge = row.querySelector('.status-badge');
                        const actionCellWrapper = row.querySelector('td:last-child .flex') || row.querySelector('td:last-child');
                        // ... old logic for updating row content ...
                    }

                } else {
                    showAjaxNotification(data.message || 'Operasi gagal', 'error');
                }
            } catch (err) {
                closeModal();
                showAjaxNotification('Terjadi error: ' + (err.message || err), 'error');
            } finally {
                // Hide loading
                if (btnText) btnText.classList.remove('hidden');
                if (btnLoading) { btnLoading.classList.add('hidden'); btnLoading.classList.remove('flex'); }
                this.disabled = false;
                modalCancelBtn.disabled = false;
            }
        });

        // Re-attach listeners on load/DOM changes
        document.addEventListener('DOMContentLoaded', attachModalListeners);
        window.addEventListener('load', attachModalListeners);
    </script>

</body>
</html>
