document.addEventListener('DOMContentLoaded', () => {

// --- Search Functionality (Active) ---
    const searchInput = document.getElementById('siteSearch');
    const siteCards = document.querySelectorAll('.site-card');
    let allSites = Array.from(siteCards).map(card => ({
        element: card,
        text: (card.querySelector('h3').textContent + card.querySelector('h3').title).toLowerCase()
    }));

    if (searchInput) {
        let timeout;
        const noResultsMsg = document.createElement('div');
        noResultsMsg.id = 'noResults';
        noResultsMsg.className = 'no-results';
        noResultsMsg.textContent = 'No sites found';
        noResultsMsg.style.cssText = 'grid-column: 1 / -1; text-align: center; padding: 4rem 2rem; color: var(--text-secondary); font-size: 1.2rem;';
        
        searchInput.addEventListener('input', (e) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const term = e.target.value.toLowerCase().trim();
                let visibleCount = 0;
                allSites.forEach(site => {
                    if (term === '' || site.text.includes(term)) {
                        site.element.style.display = 'block';
                        site.element.style.opacity = '1';
                        site.element.style.transform = 'translateY(0)';
                        visibleCount++;
                    } else {
                        site.element.style.opacity = '0';
                        site.element.style.transform = 'translateY(-10px)';
                        setTimeout(() => site.element.style.display = 'none', 300);
                    }
                });
                
                const siteList = document.querySelector('.site-list');
                const existingMsg = siteList.querySelector('#noResults');
                if (visibleCount === 0 && term !== '') {
                    if (!existingMsg) siteList.appendChild(noResultsMsg);
                } else if (existingMsg) {
                    existingMsg.remove();
                }
            }, 250);
        });
        
        // Smooth transitions
        const style = document.createElement('style');
        style.textContent = `
            .site-card { transition: all 0.3s ease; }
            .no-results { background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border-radius: 16px; border: 1px solid var(--glass-border); }
        `;
        document.head.appendChild(style);
    }
    // console.log('Search script loaded');
    // console.log('Search input:', document.getElementById('siteSearch'));
    // console.log('Site list:', document.getElementById('siteList'));
    // console.log('Site cards:', document.querySelectorAll('.site-card').length);
    // if (searchInput && siteList) {
    //     let allSites = [];
        
    //     // جمع‌آوری داده‌های سایت‌ها
    //     function initSearch() {
    //         allSites = Array.from(siteList.querySelectorAll('.site-card')).map(card => {
    //             return {
    //                 element: card,
    //                 url: card.querySelector('h3').textContent.toLowerCase(),
    //                 title: card.querySelector('h3').getAttribute('title')?.toLowerCase() || ''
    //             };
    //         });
    //     }
        
    //     // فیلتر کردن با debounce
    //     let searchTimeout;
    //     searchInput.addEventListener('input', function() {
    //         clearTimeout(searchTimeout);
    //         searchTimeout = setTimeout(() => {
    //             filterSites(this.value.toLowerCase().trim());
    //         }, 300);
    //     });
        
    //     function filterSites(searchTerm) {
    //         if (!allSites.length) initSearch();
            
    //         if (searchTerm === '') {
    //             allSites.forEach(site => {
    //                 site.element.style.display = 'block';
    //                 site.element.style.opacity = '1';
    //                 site.element.style.transform = 'translateY(0)';
    //             });
    //             return;
    //         }
            
    //         allSites.forEach(site => {
    //             const matches = site.url.includes(searchTerm) || site.title.includes(searchTerm);
                
    //             if (matches) {
    //                 site.element.style.display = 'block';
    //                 setTimeout(() => {
    //                     site.element.style.opacity = '1';
    //                     site.element.style.transform = 'translateY(0)';
    //                 }, 10);
    //             } else {
    //                 site.element.style.opacity = '0';
    //                 site.element.style.transform = 'translateY(10px)';
    //                 setTimeout(() => {
    //                     site.element.style.display = 'none';
    //                 }, 300);
    //             }
    //         });
    //     }
        
    //     // اضافه کردن transition برای انیمیشن
    //     const style = document.createElement('style');
    //     style.textContent = `
    //         .site-card {
    //             transition: all 0.3s ease;
    //         }
    //     `;
    //     document.head.appendChild(style);
        
    //     initSearch();
    // }

    // --- Modal Logic ---
    const modals = document.querySelectorAll('.modal');
    const closeBtns = document.querySelectorAll('.close');

    // تابع باز کردن مودال
    window.openModal = (modalId) => {
        document.getElementById(modalId).style.display = 'block';
    };

    // تابع بستن مودال
    window.closeModal = (modalId) => {
        document.getElementById(modalId).style.display = 'none';
    };

    // دکمه‌های بستن
    closeBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.target.closest('.modal').style.display = 'none';
        });
    });

    // بستن با کلیک بیرون مودال
    window.addEventListener('click', (e) => {
        if (e.target.classList.contains('modal')) {
            e.target.style.display = 'none';
        }
    });

    // --- Auth modal: login / forgot password views ---
    const authLoginView = document.getElementById('authLoginView');
    const authForgotView = document.getElementById('authForgotView');
    const showForgotPassword = document.getElementById('showForgotPassword');
    const backToLogin = document.getElementById('backToLogin');

    const showAuthLoginView = () => {
        if (authLoginView) authLoginView.style.display = 'block';
        if (authForgotView) authForgotView.style.display = 'none';
    };

    const showAuthForgotView = () => {
        if (authLoginView) authLoginView.style.display = 'none';
        if (authForgotView) authForgotView.style.display = 'block';
    };

    if (showForgotPassword) {
        showForgotPassword.addEventListener('click', (e) => {
            e.preventDefault();
            showAuthForgotView();
        });
    }

    if (backToLogin) {
        backToLogin.addEventListener('click', (e) => {
            e.preventDefault();
            showAuthLoginView();
        });
    }

    const originalOpenModal = window.openModal;
    window.openModal = (modalId) => {
        if (modalId === 'authModal') showAuthLoginView();
        originalOpenModal(modalId);
    };

    // --- Login Modal ---
    const loginBtn = document.getElementById('loginBtn');
    if (loginBtn) {
        loginBtn.addEventListener('click', () => openModal('authModal'));
    }

    // --- Auth Form Submit (AJAX) ---
    const authForm = document.getElementById('authForm');
    if (authForm) {
        authForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(authForm);

            fetch('auth.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message); // نمایش خطا (در آینده می‌توان بهتر کرد)
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    const forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(forgotForm);
            formData.append('ajax', '1');

            const submitBtn = forgotForm.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            try {
                const resetUrl = new URL('password_reset_request.php', window.location.href).href;
                const response = await fetch(resetUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json' },
                    body: formData,
                });

                let data;
                try {
                    data = await response.json();
                } catch {
                    alert('Server error. Please try again.');
                    return;
                }

                alert(data.message || 'Request completed.');
                if (data.success) {
                    forgotForm.reset();
                    showAuthLoginView();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error. Please check your connection and try again.');
            } finally {
                if (submitBtn) submitBtn.disabled = false;
            }
        });
    }

    // --- New Site Modal ---
    const newSiteBtn = document.getElementById('newSiteBtn');
    // if (newSiteBtn) {
    //     newSiteBtn.addEventListener('click', () => openModal('newSiteModal'));
    // }

    // --- New Site Modal ---
    if (newSiteBtn) {
        newSiteBtn.addEventListener('click', () => openModal('newSiteModal'));
    }

    // ✅ کد جدید برای اصلاح لینک قبل از ارسال
    const newSiteForm = document.getElementById('newSiteForm');
    if (newSiteForm) {
        newSiteForm.addEventListener('submit', (e) => {
            e.preventDefault(); // جلوگیری از ارسال پیش‌فرض فرم
            
            const urlInput = document.getElementById('siteUrl');
            let urlValue = urlInput.value.trim();

            // ✅ بررسی و اصلاح پروتکل
            // اگر لینک با http:// یا https:// شروع نشده باشد، https:// را به آن اضافه کن
            if (!urlValue.match(/^https?:\/\//)) {
                urlValue = 'https://' + urlValue;
                urlInput.value = urlValue; // مقدار اینپوت را هم آپدیت می‌کنیم تا کاربر ببیند
            }

            // ✅ ارسال فرم با استفاده از AJAX (یا ارسال دستی)
            // در اینجا ما فرم را به صورت دستی Submit می‌کنیم تا به create_site.php برود
            // چون قبلاً e.preventDefault() کردیم، حالا باید دستی ارسال کنیم
            newSiteForm.submit();
        });
    }


});

    // --- Share Access Logic (Dashboard) ---
    const shareBtns = document.querySelectorAll('.btn-share');
    const shareModal = document.getElementById('shareModal');
    const shareForm = document.getElementById('shareForm');
    const accessList = document.getElementById('accessList');

    shareBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const siteId = btn.dataset.id;
            document.getElementById('shareSiteId').value = siteId;
            openModal('shareModal');
            loadAccessList(siteId, 'accessList'); // لود لیست
        });
    });

    // لود کردن لیست دسترسی‌ها
    function loadAccessList(siteId, listElementId) {
        const listEl = document.getElementById(listElementId);
        listEl.innerHTML = '<li>Loading...</li>';

        fetch(`get_access_list.php?siteId=${siteId}`)
            .then(res => res.json())
            .then(response => {
                listEl.innerHTML = '';
                if (response.success && response.data.length > 0) {
                    response.data.forEach(item => {
                        const li = document.createElement('li');
                        li.innerHTML = `
                            <span>${item.shared_with_email}</span>
                            <button class="btn-remove-access" onclick="removeAccess('${siteId}', '${item.shared_with_email}', '${listElementId}')">Remove</button>
                        `;
                        listEl.appendChild(li);
                    });
                } else {
                    listEl.innerHTML = '<li>No access granted yet.</li>';
                }
            });
    }

    // تابع حذف دسترسی (Global تا در HTML قابل فراخوانی باشد)
    window.removeAccess = function(siteId, email, listElementId) {
        if(!confirm('Are you sure you want to remove access for ' + email + '?')) return;

        const formData = new FormData();
        formData.append('siteId', siteId);
        formData.append('email', email);

        fetch('remove_access.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    loadAccessList(siteId, listElementId); // رفرش لیست
                } else {
                    alert(data.message);
                }
            });
    };

    // ارسال فرم شیر (قبلا بود، فقط تابع رفرش لیست اضافه شد)
    if (shareForm) {
        shareForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(shareForm);
            const siteId = document.getElementById('shareSiteId').value;

            fetch('share_site.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        shareForm.reset();
                        loadAccessList(siteId, 'accessList'); // رفرش لیست
                    } else {
                        alert(data.message);
                    }
                });
        });
    }

    // --- دکمه Delete ---
// --- Delete Site Logic ---
//const deleteBtns = document.querySelectorAll('.btn-delete');

// deleteBtns.forEach(btn => {
//     btn.addEventListener('click', function() {
//         const siteId = this.getAttribute('data-id');
//         const siteCard = this.closest('.site-card'); // پیدا کردن کارت سایت برای حذف از DOM

//         if (confirm('Are you sure you want to delete this site? This action cannot be undone.')) {
//             const formData = new FormData();
//             formData.append('siteId', siteId);

//             fetch('delete_site.php', {
//                 method: 'POST',
//                 body: formData
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     // حذف موفقیت‌آمیز: کارت را از صفحه حذف کن
//                     if (siteCard) {
//                         siteCard.remove();
//                     }
//                     // نمایش پیام موفقیت (اختیاری)
//                     alert('Site deleted successfully.');
//                 } else {
//                     // نمایش خطا
//                     alert('Error: ' + data.message);
//                 }
//             })
//             .catch(error => {
//                 console.error('Error:', error);
//                 alert('An error occurred while deleting the site.');
//             });
//         }
//     });

    
// });

// --- Delete Site Logic (With Custom Modal) ---
const deleteBtns = document.querySelectorAll('.btn-delete');
const deleteModal = document.getElementById('deleteModal');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
const deleteModalClose = deleteModal.querySelector('.close');

let siteToDeleteId = null; // متغیر برای نگهداری ID سایت در حال حذف

// ۱. باز کردن مودال وقتی روی دکمه Delete کلیک می‌شود
deleteBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        siteToDeleteId = this.getAttribute('data-id'); // ذخیره ID سایت
        deleteModal.style.display = 'block'; // باز کردن مودال
    });
});

// ۲. عملیات حذف وقتی روی دکمه "Yes, Delete" کلیک می‌شود
confirmDeleteBtn.addEventListener('click', () => {
    if (siteToDeleteId) {
        const formData = new FormData();
        formData.append('siteId', siteToDeleteId);

        fetch('delete_site.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // پیدا کردن کارت سایت در صفحه و حذف آن
                const btn = document.querySelector(`.btn-delete[data-id="${siteToDeleteId}"]`);
                if (btn) {
                    const siteCard = btn.closest('.site-card');
                    if (siteCard) siteCard.remove();
                }
                closeModal('deleteModal'); // بستن مودال
                // می‌توانید یک پیام موفقیت (Toast) هم اضافه کنید
            } else {
                alert('Error: ' + data.message); // در صورت خطا از الرت استفاده می‌کنیم
                closeModal('deleteModal');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the site.');
            closeModal('deleteModal');
        });
    }
});

// ۳. بستن مودال با دکمه Cancel
cancelDeleteBtn.addEventListener('click', () => {
    closeModal('deleteModal');
    siteToDeleteId = null; // پاک کردن ID
});

// // ۴. بستن مودال با دکمه X (ضربدر)
// deleteModalClose.addEventListener('click', () => {
//     closeModal('deleteModal');
//     siteToDeleteId = null;
// });