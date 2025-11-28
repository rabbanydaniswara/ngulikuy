 </div>

    <nav class="mobile-nav md:hidden">
        <div class="flex justify-around items-center">
            <a href="?tab=home" class="mobile-nav-item <?php echo $active_tab === 'home' ? 'active' : ''; ?>">
                <i data-feather="home"></i>
                <span>Home</span>
            </a>
            <a href="?tab=search" class="mobile-nav-item <?php echo $active_tab === 'search' ? 'active' : ''; ?>">
                <i data-feather="search"></i>
                <span>Cari Pekerja</span>
            </a>
            <a href="?tab=post_job" class="mobile-nav-item <?php echo $active_tab === 'post_job' ? 'active' : ''; ?>">
                <i data-feather="plus-circle"></i>
                <span>Post</span>
            </a>
            <a href="?tab=my_jobs" class="mobile-nav-item <?php echo $active_tab === 'my_jobs' ? 'active' : ''; ?>">
                <i data-feather="briefcase"></i>
                <span>Pekerja Saya</span>
            </a>
            <a href="?tab=orders" class="mobile-nav-item <?php echo $active_tab === 'orders' ? 'active' : ''; ?>">
                <i data-feather="clipboard"></i>
                <span>Pesanan</span>
            </a>
        </div>
    </nav>

    <?php include __DIR__ . '/_modals.php'; ?>

    <div id="viewWorkerModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity modal-overlay" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="gradient-bg p-6 text-center text-white rounded-t-lg relative">
                    <h2 class="text-xl font-bold">Detail Data Pekerja</h2>
                    <p id="viewWorkerTitle" class="text-blue-100 mt-1"></p>
                    <button type="button" id="closeViewWorkerModalX" class="absolute top-4 right-4 text-blue-100 hover:text-white transition p-1 rounded-full hover:bg-white/10">
                        <i data-feather="x" class="w-6 h-6"></i>
                    </button>
                </div>
                <div class="p-6 modal-content">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="md:col-span-1 flex justify-center">
                            <img id="viewWorkerPhoto" class="photo-preview rounded-full w-40 h-40 object-cover border-4 border-white shadow-lg">
                        </div>
                        <div class="md:col-span-2">
                            <h3 id="viewWorkerName" class="text-2xl font-bold text-gray-800"></h3>
                            <p id="viewWorkerEmail" class="text-sm text-gray-500"></p>
                            <p id="viewWorkerPhone" class="text-sm text-gray-500"></p>
                            <div class="mt-4">
                                <span id="viewWorkerStatus" class="px-3 py-1 text-sm rounded-full"></span>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 border-t pt-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi</label>
                            <p id="viewWorkerLocation" class="text-base text-gray-800"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Rate per Hari</label>
                            <p id="viewWorkerRate" class="text-base text-gray-800"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Pengalaman</label>
                            <p id="viewWorkerExperience" class="text-base text-gray-800"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Bergabung pada</label>
                            <p id="viewWorkerJoinDate" class="text-base text-gray-800"></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Keahlian</label>
                            <div id="viewWorkerSkills" class="flex flex-wrap gap-2"></div>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500 mb-1">Deskripsi</label>
                            <p id="viewWorkerDescription" class="text-base text-gray-800"></p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse rounded-b-lg">
                    <button type="button" id="closeViewWorkerModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">Tutup</button>
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
        const closeViewWorkerModalXBtn = document.getElementById('closeViewWorkerModalX');
        const closeViewWorkerModalBtn = document.getElementById('closeViewWorkerModal');

        function openViewWorkerModal(workerId) {
            const workers = <?php echo json_encode($allWorkersForModal); ?>;
            const worker = workers[workerId];
            if (worker) {
                document.getElementById('viewWorkerName').textContent = worker.name;
                document.getElementById('viewWorkerTitle').textContent = 'ID: ' + worker.id;
                document.getElementById('viewWorkerEmail').textContent = worker.email;
                document.getElementById('viewWorkerPhone').textContent = worker.phone;
                document.getElementById('viewWorkerLocation').textContent = worker.location;
                document.getElementById('viewWorkerRate').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(worker.rate) + '/hari';
                document.getElementById('viewWorkerExperience').textContent = worker.experience || '-';
                document.getElementById('viewWorkerJoinDate').textContent = new Date(worker.joinDate).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
                document.getElementById('viewWorkerDescription').textContent = worker.description || '-';
                document.getElementById('viewWorkerPhoto').src = worker.photo || 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&h=150&fit=crop&crop=face';
                
                const statusSpan = document.getElementById('viewWorkerStatus');
                statusSpan.textContent = worker.status;
                statusSpan.className = 'px-3 py-1 text-sm rounded-full ' + (worker.status === 'Available' ? 'status-available' : (worker.status === 'Assigned' ? 'status-assigned' : 'status-on-leave'));

                const skillsContainer = document.getElementById('viewWorkerSkills');
                skillsContainer.innerHTML = '';
                if (worker.skills && worker.skills.length > 0) {
                    worker.skills.forEach(skill => {
                        const skillBadge = document.createElement('span');
                        skillBadge.className = 'bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full';
                        skillBadge.textContent = skill;
                        skillsContainer.appendChild(skillBadge);
                    });
                } else {
                    skillsContainer.textContent = '-';
                }

                viewWorkerModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeViewWorkerModal() {
            if(viewWorkerModal) viewWorkerModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        viewWorkerBtns.forEach(btn => btn.addEventListener('click', function() {
            const workerId = this.dataset.workerId;
            openViewWorkerModal(workerId);
        }));

        if(closeViewWorkerModalXBtn) closeViewWorkerModalXBtn.addEventListener('click', closeViewWorkerModal);
        if(closeViewWorkerModalBtn) closeViewWorkerModalBtn.addEventListener('click', closeViewWorkerModal);
        
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
        
        // Prevent body scroll when modal is open
        const modals = document.querySelectorAll('[id$="Modal"]');
        modals.forEach(modal => {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        const isHidden = modal.classList.contains('hidden');
                        document.body.style.overflow = isHidden ? 'auto' : 'hidden';
                    }
                });
            });
            observer.observe(modal, { attributes: true });
        });
        
        // Smooth scroll to top when changing tabs
        const navLinks = document.querySelectorAll('a[href^="?tab="]');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
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
        
        // Touch-friendly hover effects on mobile
        if ('ontouchstart' in window) {
            document.querySelectorAll('.card-hover').forEach(card => {
                card.addEventListener('touchstart', function() {
                    this.style.transform = 'translateY(-4px)';
                });
                card.addEventListener('touchend', function() {
                    this.style.transform = '';
                });
            });
        }
        
        // Lazy load images
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                            img.removeAttribute('data-src');
                        }
                        observer.unobserve(img);
                    }
                });
            });
            
            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
        
        // Form validation enhancement
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.disabled) {
                    submitBtn.disabled = true;
                    submitBtn.classList.add('btn-loading');
                    
                    // Re-enable after 3 seconds as fallback
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.classList.remove('btn-loading');
                    }, 3000);
                }
            });
        });
        
        // Date input validation
        document.querySelectorAll('input[type="date"]').forEach(input => {
            input.addEventListener('change', function() {
                const startDate = document.querySelector('input[name="start_date"]');
                const endDate = document.querySelector('input[name="end_date"]');
                
                if (startDate && endDate && startDate.value && endDate.value) {
                    if (new Date(endDate.value) < new Date(startDate.value)) {
                        endDate.value = startDate.value;
                        alert('Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
                    }
                }
            });
        });
        
        // PWA-like experience: cache scroll position
        let scrollPosition = 0;
        window.addEventListener('scroll', () => {
            scrollPosition = window.scrollY;
        });
        
        window.addEventListener('beforeunload', () => {
            sessionStorage.setItem('scrollPosition', scrollPosition);
        });
        
        window.addEventListener('load', () => {
            const savedPosition = sessionStorage.getItem('scrollPosition');
            if (savedPosition) {
                window.scrollTo(0, parseInt(savedPosition));
                sessionStorage.removeItem('scrollPosition');
            }
        });
        
        // Network status indicator
        function updateOnlineStatus() {
            const status = navigator.onLine ? 'online' : 'offline';
            if (status === 'offline') {
                const banner = document.createElement('div');
                banner.id = 'offline-banner';
                banner.className = 'fixed top-0 left-0 right-0 bg-red-500 text-white text-center py-2 text-sm z-50';
                banner.textContent = '⚠️ Tidak ada koneksi internet';
                document.body.prepend(banner);
            } else {
                const banner = document.getElementById('offline-banner');
                if (banner) banner.remove();
            }
        }
        
        window.addEventListener('online', updateOnlineStatus);
        window.addEventListener('offline', updateOnlineStatus);
        
        // Initial check
        updateOnlineStatus();
        
        // Performance: Defer non-critical JavaScript
        if ('requestIdleCallback' in window) {
            requestIdleCallback(() => {
                console.log('App loaded successfully');
            });
        }
        
        // Console welcome message
        console.log('%cNguliKuy Dashboard', 'color: #3b82f6; font-size: 24px; font-weight: bold;');
        console.log('%cPlatform Booking Pekerja Harian Terpercaya', 'color: #6b7280; font-size: 14px;');

        // --- Generic Action Modal & AJAX Logic ---
        const CSRF_TOKEN = '<?php echo getCsrfToken(); ?>';

        const ajaxNotification = document.createElement('div');
        ajaxNotification.id = 'ajax-notification';
        ajaxNotification.setAttribute('role', 'status');
        ajaxNotification.setAttribute('aria-live', 'polite');
        Object.assign(ajaxNotification.style, {
            position: 'fixed',
            bottom: '20px',
            right: '20px',
            padding: '10px 20px',
            borderRadius: '6px',
            color: 'white',
            zIndex: '1000',
            display: 'none',
            transition: 'opacity 0.4s ease-in-out'
        });
        document.body.appendChild(ajaxNotification);

        function showAjaxNotification(message, type = 'success') {
            ajaxNotification.textContent = message;
            ajaxNotification.className = '';
            ajaxNotification.classList.add(type === 'success' ? 'success' : 'error');
            if(type === 'success') ajaxNotification.style.backgroundColor = '#10B981';
            else ajaxNotification.style.backgroundColor = '#EF4444';
            
            ajaxNotification.style.display = 'block';
            ajaxNotification.style.opacity = '1';
            setTimeout(() => {
                ajaxNotification.style.opacity = '0';
                setTimeout(() => { ajaxNotification.style.display = 'none'; }, 500);
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
                    title = 'Hapus Pekerja?';
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
                
                modalConfirmBtn.className = modalConfirmBtn.className.replace(/bg-\\S+\\s?/g, '').replace(/hover:bg-\\S+\\s?/g, '');
                modalConfirmBtn.classList.add(...confirmClass.split(' '));
                
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
