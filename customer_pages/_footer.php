    </div> <!-- End Main Content Wrapper -->

    <!-- Mobile Navigation (Fixed Bottom) -->
    <nav class="mobile-nav md:hidden">
        <div class="grid grid-cols-5 h-full">
            <a href="?tab=home" class="mobile-nav-item <?php echo $active_tab === 'home' ? 'active' : ''; ?>">
                <i data-feather="home"></i>
                <span>Home</span>
            </a>
            <a href="?tab=search" class="mobile-nav-item <?php echo $active_tab === 'search' ? 'active' : ''; ?>">
                <i data-feather="search"></i>
                <span>Cari</span>
            </a>
            <a href="?tab=post_job" class="mobile-nav-item <?php echo $active_tab === 'post_job' ? 'active' : ''; ?>">
                <div class="bg-blue-600 rounded-full p-2 -mt-6 shadow-lg border-4 border-white text-white">
                    <i data-feather="plus" class="w-6 h-6"></i>
                </div>
                <span class="mt-1">Post</span>
            </a>
            <a href="?tab=my_jobs" class="mobile-nav-item <?php echo $active_tab === 'my_jobs' ? 'active' : ''; ?>">
                <i data-feather="briefcase"></i>
                <span>Pekerja</span>
            </a>
            <a href="?tab=orders" class="mobile-nav-item <?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
                <i data-feather="clipboard"></i>
                <span>Pesanan</span>
            </a>
        </div>
    </nav>

    <?php include __DIR__ . '/_modals.php'; ?>

    <!-- View Worker Detail Modal -->
    <div id="viewWorkerModal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="closeViewWorkerModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <!-- Modal Header -->
                <div class="bg-white px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Detail Pekerja</h3>
                        <p id="viewWorkerTitle" class="text-xs text-gray-500 font-mono mt-0.5"></p>
                    </div>
                    <button type="button" onclick="closeViewWorkerModal()" class="text-gray-400 hover:text-gray-500 p-2 rounded-full hover:bg-gray-100 transition-colors">
                        <i data-feather="x" class="w-5 h-5"></i>
                    </button>
                </div>

                <div class="p-6 modal-content">
                    <div class="flex flex-col md:flex-row gap-6 mb-8">
                        <!-- Photo -->
                        <div class="flex-shrink-0 flex justify-center md:justify-start">
                            <img id="viewWorkerPhoto" class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-gray-50 shadow-sm">
                        </div>
                        
                        <!-- Main Info -->
                        <div class="flex-1 text-center md:text-left">
                            <h3 id="viewWorkerName" class="text-2xl font-bold text-gray-900 mb-1"></h3>
                            <div class="flex flex-wrap justify-center md:justify-start gap-2 mb-3">
                                <span id="viewWorkerStatus" class="status-badge"></span>
                            </div>
                            
                            <div class="flex flex-col md:flex-row gap-4 text-sm text-gray-600 justify-center md:justify-start">
                                <div class="flex items-center gap-1.5">
                                    <i data-feather="mail" class="w-4 h-4 text-gray-400"></i>
                                    <span id="viewWorkerEmail"></span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <i data-feather="phone" class="w-4 h-4 text-gray-400"></i>
                                    <span id="viewWorkerPhone"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Lokasi</label>
                            <p id="viewWorkerLocation" class="text-gray-900 font-medium"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Tarif Harian</label>
                            <p id="viewWorkerRate" class="text-gray-900 font-medium"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Pengalaman</label>
                            <p id="viewWorkerExperience" class="text-gray-900 font-medium"></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Bergabung</label>
                            <p id="viewWorkerJoinDate" class="text-gray-900 font-medium"></p>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Keahlian</label>
                            <div id="viewWorkerSkills" class="flex flex-wrap gap-2"></div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Tentang</label>
                            <p id="viewWorkerDescription" class="text-gray-600 text-sm leading-relaxed"></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-6 py-4 flex justify-end">
                    <button type="button" onclick="closeViewWorkerModal()" class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 font-medium hover:bg-gray-50 transition-colors shadow-sm text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize feather icons
        feather.replace();
        
        // Re-initialize feather icons after any DOM changes
        function refreshIcons() {
            feather.replace();
        }
        
        // Modal functions
        function openBookingModal(workerId) {
            document.getElementById('modal_worker_id').value = workerId;
            document.getElementById('bookingModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(refreshIcons, 100);
        }
        
        function closeBookingModal() {
            document.getElementById('bookingModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function openReviewModal(jobId, workerId) {
            document.getElementById('review_job_id').value = jobId;
            document.getElementById('review_worker_id').value = workerId;
            document.getElementById('reviewModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            setTimeout(refreshIcons, 100);
        }
        
        function closeReviewModal() {
            document.getElementById('reviewModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        const viewWorkerModal = document.getElementById('viewWorkerModal');
        const viewWorkerBtns = document.querySelectorAll('.view-worker-btn');

        function openViewWorkerModal(workerId) {
            const workers = <?php echo json_encode($allWorkersForModal ?? []); ?>;
            const worker = workers[workerId];
            if (worker) {
                document.getElementById('viewWorkerName').textContent = worker.nama;
                document.getElementById('viewWorkerTitle').textContent = 'ID: ' + worker.id_pekerja;
                document.getElementById('viewWorkerEmail').textContent = worker.email;
                document.getElementById('viewWorkerPhone').textContent = worker.telepon;
                document.getElementById('viewWorkerLocation').textContent = worker.lokasi;
                document.getElementById('viewWorkerRate').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(worker.tarif_per_jam);
                document.getElementById('viewWorkerExperience').textContent = worker.pengalaman || '-';
                document.getElementById('viewWorkerJoinDate').textContent = new Date(worker.tanggal_bergabung).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                document.getElementById('viewWorkerDescription').textContent = worker.deskripsi_diri || 'Tidak ada deskripsi.';
                document.getElementById('viewWorkerPhoto').src = worker.url_foto || 'https://ui-avatars.com/api/?name=' + encodeURIComponent(worker.nama) + '&background=random';
                
                const statusSpan = document.getElementById('viewWorkerStatus');
                statusSpan.textContent = worker.status_ketersediaan;
                
                // Set status class
                statusSpan.className = 'status-badge';
                if (worker.status_ketersediaan === 'Available') statusSpan.classList.add('status-completed'); // Green
                else if (worker.status_ketersediaan === 'Assigned') statusSpan.classList.add('status-in-progress'); // Blue
                else statusSpan.classList.add('status-cancelled'); // Red

                const skillsContainer = document.getElementById('viewWorkerSkills');
                skillsContainer.innerHTML = '';
                if (worker.keahlian && worker.keahlian.length > 0) {
                    worker.keahlian.forEach(skill => {
                        const skillBadge = document.createElement('span');
                        skillBadge.className = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100';
                        skillBadge.textContent = skill;
                        skillsContainer.appendChild(skillBadge);
                    });
                } else {
                    skillsContainer.textContent = '-';
                }

                viewWorkerModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
                refreshIcons();
            }
        }

        function closeViewWorkerModal() {
            if(viewWorkerModal) viewWorkerModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        // Event Delegation for dynamic elements
        document.body.addEventListener('click', function(e) {
            if (e.target.closest('.view-worker-btn')) {
                const btn = e.target.closest('.view-worker-btn');
                const workerId = btn.dataset.workerId;
                openViewWorkerModal(workerId);
            }
        });
        
        // Close modals on overlay click
        document.getElementById('bookingModal').addEventListener('click', function(e) {
            if (e.target === this) closeBookingModal();
        });
        
        document.getElementById('reviewModal').addEventListener('click', function(e) {
            if (e.target === this) closeReviewModal();
        });
        
        // Close modals on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeBookingModal();
                closeReviewModal();
                closeViewWorkerModal();
            }
        });
        
        // Auto-dismiss alert notifications
        const alerts = document.querySelectorAll('.alert-notification');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        });
        
        // Console welcome message
        console.log('%cNguliKuy Dashboard', 'color: #2563eb; font-size: 20px; font-weight: bold;');

        // --- Generic Action Modal & AJAX Logic ---
        const CSRF_TOKEN = '<?php echo getCsrfToken(); ?>';

        const ajaxNotification = document.createElement('div');
        ajaxNotification.id = 'ajax-notification';
        ajaxNotification.className = 'toast-notification hidden bg-white border shadow-lg rounded-xl p-4 flex items-center gap-3';
        document.body.appendChild(ajaxNotification);

        function showAjaxNotification(message, type = 'success') {
            const icon = type === 'success' ? 'check-circle' : 'alert-circle';
            const colorClass = type === 'success' ? 'text-green-600' : 'text-red-600';
            const bgClass = type === 'success' ? 'bg-green-50' : 'bg-red-50';
            
            ajaxNotification.innerHTML = `
                <div class="w-8 h-8 ${bgClass} rounded-lg flex items-center justify-center flex-shrink-0">
                    <i data-feather="${icon}" class="w-5 h-5 ${colorClass}"></i>
                </div>
                <p class="text-sm font-medium text-gray-800">${message}</p>
            `;
            
            ajaxNotification.classList.remove('hidden');
            feather.replace();
            
            setTimeout(() => {
                ajaxNotification.classList.add('hidden');
            }, 4000);
        }

        const actionModal = document.getElementById('actionModal');
        if(actionModal) {
            const modalOverlay = document.getElementById('modal-overlay');
            const modalTitle = document.getElementById('modal-title');
            const modalDescription = document.getElementById('modal-description');
            const modalConfirmBtn = document.getElementById('modal-confirm-btn');
            const modalCancelBtn = document.getElementById('modal-cancel-btn');
            const modalIcon = document.getElementById('modal-icon');
            const modalIconContainer = document.getElementById('modal-icon-container');

            function closeModal() {
                actionModal.classList.add('hidden');
            }

            function openModalHandler() {
                const action = this.dataset.action;
                const jobId = this.dataset.jobId;
                const jobTitle = this.dataset.jobTitle;

                let title = 'Konfirmasi Tindakan';
                let description = 'Apakah Anda yakin?';
                let confirmText = 'Konfirmasi';
                let confirmClass = 'bg-blue-600 hover:bg-blue-700';
                let iconName = 'info';
                let iconClass = 'text-blue-600';
                let iconContainerClass = 'bg-blue-100';

                if (action === 'customer_delete_posted_job') {
                    title = 'Hapus Pekerjaan?';
                    description = `Anda akan menghapus pekerjaan <strong>"${jobTitle}"</strong>. Tindakan ini tidak dapat dibatalkan.`;
                    confirmText = 'Ya, Hapus';
                    confirmClass = 'bg-red-600 hover:bg-red-700';
                    iconName = 'trash-2';
                    iconClass = 'text-red-600';
                    iconContainerClass = 'bg-red-100';
                }

                modalTitle.textContent = title;
                modalDescription.innerHTML = description;
                modalConfirmBtn.querySelector('.btn-text').textContent = confirmText;
                
                modalConfirmBtn.className = `w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 text-base font-medium text-white sm:ml-3 sm:w-auto sm:text-sm ${confirmClass}`;
                
                modalIcon.setAttribute('data-feather', iconName);
                modalIcon.className = `h-6 w-6 ${iconClass}`;
                modalIconContainer.className = `mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full sm:mx-0 sm:h-10 sm:w-10 ${iconContainerClass}`;
                feather.replace();

                modalConfirmBtn.dataset.action = action;
                modalConfirmBtn.dataset.jobId = jobId;

                actionModal.classList.remove('hidden');
            }

            document.querySelectorAll('.job-modal-trigger').forEach(button => {
                button.addEventListener('click', openModalHandler);
            });

            modalCancelBtn.addEventListener('click', closeModal);
            modalOverlay.addEventListener('click', closeModal);

            modalConfirmBtn.addEventListener('click', async function() {
                const action = this.dataset.action;
                const jobId = this.dataset.jobId;
                const row = document.getElementById('job-row-' + jobId);

                const btnText = this.querySelector('.btn-text');
                const btnLoading = this.querySelector('.btn-loading');

                btnText.classList.add('hidden');
                btnLoading.classList.remove('hidden');
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
                        if (row) {
                            row.style.transition = 'opacity 0.5s ease';
                            row.style.opacity = '0';
                            setTimeout(() => row.remove(), 500);
                        }
                    } else {
                        showAjaxNotification(data.message || 'Operasi gagal', 'error');
                    }
                } catch (err) {
                    closeModal();
                    showAjaxNotification('Terjadi error: ' + (err.message || err), 'error');
                } finally {
                    btnText.classList.remove('hidden');
                    btnLoading.classList.add('hidden');
                    this.disabled = false;
                    modalCancelBtn.disabled = false;
                }
            });
        }
    </script>
</body>
</html>
