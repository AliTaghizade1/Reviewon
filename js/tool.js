const fontStyle = document.createElement('style');
fontStyle.innerHTML = `
  @font-face {
    font-family: 'Vazir';
    src: url('Font/vazir.ttf') format('truetype');
    font-weight: normal;
    font-style: normal;
  }
  body {
    font-family: 'Vazir', 'Segoe UI', Tahoma, sans-serif !important;
  }
`;
document.head.appendChild(fontStyle);

let currentSelector = null; // اضافه کردن این خط
let currentDevice = null; // <-- این خط را اضافه کنید
let clickX, clickY;
let commentToDeleteId = null;

window.openModal = (modalId) => {
    document.getElementById(modalId).style.display = 'block';
};

window.closeModal = (modalId) => {
    document.getElementById(modalId).style.display = 'none';
};



document.addEventListener('DOMContentLoaded', () => {

    // ✅ پاپ‌آپ اولیه (فقط یک بار برای هر سایت)
    if (!localStorage.getItem('welcomePopupSeen_' + SITE_ID)) {
        document.getElementById('welcomePopup').style.display = 'flex';
        localStorage.setItem('welcomePopupSeen_' + SITE_ID, 'true');
    }

    document.getElementById('welcomeBtn').addEventListener('click', () => {
        document.getElementById('welcomePopup').style.display = 'none';
    });

    let isAnimatingComment = false; // قفل برای جلوگیری از تداخل
    const iframe = document.getElementById('siteFrame');
    const iframeWrapper = document.getElementById('iframeWrapper');
    const deviceBtns = document.querySelectorAll('.device-btn');
    const commentModal = document.getElementById('commentModal');
    const commentForm = document.getElementById('commentForm');
    const commentsList = document.getElementById('commentsList');
    let allComments = [];


// ✅ تابع اصلاح شده و قدرتمند برای نرمال‌سازی URL
function normalizeUrl(url) {
    if (!url) return '';
    
    // ۱. حذف اسلش آخر (Trailing Slash)
    let normalized = url.endsWith('/') ? url.slice(0, -1) : url;
    
    // ۲. حذف پارامترهای اضافی (مثل ?utm_source=...) اگر وجود داشته باشند
    // این بخش باعث می‌شود اگر آدرس با پارامتر لود شد، با آدرس اصلی دیتابیس یکی شود
    if (normalized.includes('?')) {
        normalized = normalized.split('?')[0];
    }
    
    // ۳. نرمال‌سازی پروتکل (http و https یکی شوند)
    // اگر سایت شما هم روی http و هم https باز می‌شود، این خط ضروری است
    normalized = normalized.replace(/^https?:\/\//, '');
    
    return normalized;
}

    // --- Share Modal Logic (Inside Tool) ---
    const toolShareBtn = document.getElementById('toolShareBtn');

    let filterMode = 'current'; // پیش‌فرض: صفحه فعلی
    // ✅ اضافه کردن رویداد دراپ‌دون
    const commentFilter = document.getElementById('commentFilter');
    commentFilter.addEventListener('change', () => {
        filterMode = commentFilter.value;
        renderSidebar();
    });

    // فقط اگر دکمه وجود داشت (یعنی کاربر مالک است)، کدها اجرا شوند
    if (toolShareBtn) {
        const toolShareModal = document.getElementById('toolShareModal');
        const toolShareForm = document.getElementById('toolShareForm');
        const toolAccessList = document.getElementById('toolAccessList');

        toolShareBtn.addEventListener('click', () => {
            openModal('toolShareModal');
            loadToolAccessList(); // لود لیست
        });
        
        if (toolShareModal) {
            toolShareModal.querySelector('.close').addEventListener('click', () => closeModal('toolShareModal'));
            
            // لود لیست مخصوص ابزار
            function loadToolAccessList() {
                toolAccessList.innerHTML = '<li>Loading...</li>';
                fetch(`get_access_list.php?siteId=${SITE_ID}`)
                    .then(res => res.json())
                    .then(response => {
                        toolAccessList.innerHTML = '';
                        if (response.success && response.data.length > 0) {
                            response.data.forEach(item => {
                                const li = document.createElement('li');
                                li.innerHTML = `
                                    <span>${item.shared_with_email}</span>
                                    <button class="btn-remove-access" onclick="removeToolAccess('${item.shared_with_email}')">Remove</button>
                                `;
                                toolAccessList.appendChild(li);
                            });
                        } else {
                            toolAccessList.innerHTML = '<li>No access granted yet.</li>';
                        }
                    });
            }

            // تابع حذف مخصوص ابزار
            window.removeToolAccess = function(email) {
                if(!confirm('Remove access for ' + email + '?')) return;
                const formData = new FormData();
                formData.append('siteId', SITE_ID);
                formData.append('email', email);
                fetch('remove_access.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) {
                            loadToolAccessList(); // رفرش لیست
                        } else {
                            alert(data.message);
                        }
                    });
            };

            // ارسال فرم شیر در ابزار
            toolShareForm.addEventListener('submit', (e) => {
                e.preventDefault();
                const formData = new FormData(toolShareForm);
                fetch('share_site.php', { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            toolShareForm.reset();
                            loadToolAccessList(); // رفرش لیست
                        } else {
                            alert(data.message);
                        }
                    });
            });
        }
    }

    
    // --- 1. Load Comments ---
    loadComments();

    function loadComments() {
    return fetch(`get_comments.php?siteId=${SITE_ID}`)
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                allComments = response.data;
                renderSidebar();

                renderPins();
                return response.data;
            }
        });
}


    function renderReply(reply) {
        return `
            <div class="reply-item">
                <div class="reply-header">
                    <span class="author">${reply.author_name.split('@')[0]}</span>
                    <span class="reply-time">${formatDate(reply.created_at)}</span>
                </div>
                <div class="reply-body">${reply.content}</div>
            </div>
        `;
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }


    // --- تابع جدید برای فرمت تاریخ ---
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

    // --- 2. Render Sidebar
    function renderSidebar() {
    commentsList.innerHTML = '';
    
    if (allComments.length === 0) {
        commentsList.innerHTML = '<p style="color:#888; text-align:center;">No comments yet.</p>';
        return;
    }
    
    // ساختار درختی کامنت‌ها
    const commentMap = {};
    allComments.forEach(comment => {
        commentMap[comment.id] = comment;
        comment.replies = []; 
    });
    const rootComments = [];
    allComments.forEach(comment => {
        if (comment.parent_comment_id) {
            if (commentMap[comment.parent_comment_id]) {
                commentMap[comment.parent_comment_id].replies.push(comment);
            }
        } else {
            rootComments.push(comment);
        }
    });
    
    // ✅ تغییر کلیدی: تقسیم کامنت‌ها به دو دسته (حل نشده و حل شده)
    const unresolvedComments = [];
    const resolvedComments = [];
    
    // ✅ تغییر کلیدی: فیلتر بر اساس filterMode
    rootComments.forEach(comment => {
        const normalizedCommentUrl = normalizeUrl(comment.url);
        const normalizedSiteUrl = normalizeUrl(SITE_URL);
        // ✅ فقط اگر فیلتر روی "Current Page" باشد، URL را چک کن
        if (filterMode === 'current' && normalizedCommentUrl !== normalizedSiteUrl) {
            return; 
        }
        
        // ✅ تقسیم به دو دسته
        if (comment.is_resolved === 0) {
            unresolvedComments.push(comment);
        } else {
            resolvedComments.push(comment);
        }
    });
    
    // ✅ نمایش کامنت‌های حل نشده اول (در بالا)
    unresolvedComments.forEach(comment => {
        const item = document.createElement('div');
        item.className = `comment-item ${comment.is_resolved ? 'resolved' : ''}`;
        item.dataset.id = comment.id;
        
        const replyCount = comment.replies.length;
        const replyBadge = replyCount > 0 ? `<span class="reply-badge">💬 ${replyCount}</span>` : '';
        
        let deviceIcon = '<img class="icon-device-btn" src="image/monitor.svg" alt="image">';
        if (comment.device_type === 'mobile') deviceIcon = '<img class="icon-device-btn" src="image/monitor-2.svg" alt="image">';
        if (comment.device_type === 'tablet') deviceIcon = '<img class="icon-device-btn" src="image/monitor-1.svg" alt="image">';
        
        item.innerHTML = `
            <div class="comment-header">
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span class="device-icon">${deviceIcon}</span>
                    <span class="author">${comment.author_name.split('@')[0]}</span>
                    ${replyBadge}
                </div>
                <input type="checkbox" class="resolve-check" ${comment.is_resolved ? 'checked' : ''}>
            </div>
            <div class="comment-body">${comment.content}</div>
        `;
        commentsList.appendChild(item);
    });
    
    // ✅ سپس نمایش کامنت‌های حل شده (در پایین)
    resolvedComments.forEach(comment => {
        const item = document.createElement('div');
        item.className = `comment-item ${comment.is_resolved ? 'resolved' : ''}`;
        item.dataset.id = comment.id;
        
        const replyCount = comment.replies.length;
        const replyBadge = replyCount > 0 ? `<span class="reply-badge">💬 ${replyCount}</span>` : '';
        
        let deviceIcon = '<img class="icon-device-btn" src="image/monitor.svg" alt="image">';
        if (comment.device_type === 'mobile') deviceIcon = '<img class="icon-device-btn" src="image/monitor-2.svg" alt="image">';
        if (comment.device_type === 'tablet') deviceIcon = '<img class="icon-device-btn" src="image/monitor-1.svg" alt="image">';
        
        item.innerHTML = `
            <div class="comment-header">
                <div style="display: flex; gap: 8px; align-items: center;">
                    <span class="device-icon">${deviceIcon}</span>
                    <span class="author">${comment.author_name.split('@')[0]}</span>
                    ${replyBadge}
                </div>
                <input type="checkbox" class="resolve-check" ${comment.is_resolved ? 'checked' : ''}>
            </div>
            <div class="comment-body">${comment.content}</div>
        `;
        commentsList.appendChild(item);
    });
    
    // ... (بقیه کد Event Delegation بدون تغییر) ...
    commentsList.onclick = function(e) {
        const item = e.target.closest('.comment-item');
        if (!item) return;
        
        const commentId = item.dataset.id;
        const comment = allComments.find(c => c.id === commentId);
        
        if (e.target.classList.contains('resolve-check')) {
            toggleResolvedStatus(commentId, e.target.checked);
        } else {
            if (comment) scrollToComment(comment);
        }
    };
}


    // --- 3. Render Pins (با مختصات دقیق) ---

function renderPins() {
    // If iframe becomes cross-origin, accessing contentDocument throws SecurityError.
    // In that case, we skip pin rendering.
    let iframeDoc = null;
    try {
        iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
    } catch (e) {
        return;
    }

    if (!iframeDoc) return;

    const oldPins = iframeDoc.querySelectorAll('.rs-pin');
    oldPins.forEach(p => p.remove());
    const currentDevice = document.querySelector('.device-btn.active').dataset.device;
    
    allComments.forEach(comment => {
        // ✅ تغییر: شرط URL اضافه شد. فقط اگر URL کامنت با URL فعلی یکی بود، آیکون را نشان بده
        if (comment.url === SITE_URL && 
            comment.device_type === currentDevice && 
            comment.is_resolved === 0 && 
            comment.parent_comment_id === null) {
            try {
                const element = iframeDoc.querySelector(comment.selector);
                if (element) {
                    element.style.position = 'relative';
                    const pin = document.createElement('div');
                    pin.className = 'rs-pin';
                    pin.innerHTML = '📍';
                    pin.title = comment.content;
                    const topPos = comment.offset_y != 0 ? comment.offset_y + 'px' : '-5px';
                    const leftPos = comment.offset_x != 0 ? comment.offset_x + 'px' : '-10px';
                    pin.style.position = 'absolute';
                    pin.style.top = topPos;
                    pin.style.left = leftPos;
                    pin.style.fontSize = '20px';
                    pin.style.cursor = 'pointer';
                    pin.style.zIndex = '9999';
                    pin.style.transition = 'transform 0.2s';
                    pin.addEventListener('click', (e) => {
                        e.stopPropagation();
                        scrollToComment(comment);
                    });
                    element.appendChild(pin);
                }
            } catch (err) { console.error(err); }
        }
    });
}


    // --- 4. Scroll & Animation + Popup ---

    function scrollToComment(comment) {
        const currentDevice = document.querySelector('.device-btn.active').dataset.device;
        
        // ✅ نکته مهم: نرمال‌سازی URLها برای مقایسه دقیق
        // گاهی اوقات آدرس‌ها با / و گاهی بدون / هستند، آن‌ها را یکسان می‌کنیم
        const normalizeUrl = (url) => url.endsWith('/') ? url.slice(0, -1) : url;
        
        const currentUrlNormalized = normalizeUrl(SITE_URL);
        const commentUrlNormalized = normalizeUrl(comment.url);

        // --- helper: extract real url (non-proxy) ---
        function extractRealUrl(maybeProxyUrl) {
            if (!maybeProxyUrl || typeof maybeProxyUrl !== 'string') return '';
            // اگر خودش proxy.php باشد
            if (maybeProxyUrl.includes('proxy.php?url=')) {
                try {
                    const q = maybeProxyUrl.split('?')[1] || '';
                    const params = new URLSearchParams(q);
                    return params.get('url') || '';
                } catch (e) {
                    return '';
                }
            }
            return maybeProxyUrl;
        }

        // --- helper: build proxy iframe src from a real url ---
        function ensureProxySrc(maybeRealOrProxyUrl) {
            const realUrl = extractRealUrl(maybeRealOrProxyUrl);
            if (!realUrl) return maybeRealOrProxyUrl; // fallback
            // همیشه iframe را از طریق proxy لود کن
            return 'proxy.php?url=' + encodeURIComponent(realUrl);
        }

        // ✅ ۱. بررسی URL: اگر کامنت مربوط به صفحه دیگری است
        if (commentUrlNormalized !== currentUrlNormalized) {
            // IMPORTANT: comment.url ممکن است مستقیماً URL واقعی نباشد.
            // در ابزارهای شما ممکن است url به proxy.php یا حتی url proxy شده‌ی proxy برسد.
            // بنابراین همیشه unwrap proxy انجام می‌دهیم و سپس مجدد با url واقعی، iframe را از طریق proxy لود می‌کنیم.
            let realTargetUrl = extractRealUrl(comment.url) || comment.url;

            // اگر realTargetUrl خودش proxy.php باشد (مثلاً چون قبلاً چیزی شبیه proxy.php?url=... ذخیره شده)، دوباره unwrap می‌کنیم.
            if (typeof realTargetUrl === 'string' && realTargetUrl.includes('proxy.php?url=')) {
                realTargetUrl = extractRealUrl(realTargetUrl) || realTargetUrl;
            }

            // آدرس آی‌فریم را عوض کن (همیشه از طریق proxy)
            iframe.src = ensureProxySrc(realTargetUrl);

            // مهم: متغیر سراسری باید URL واقعی (non-proxy) باشد
            window.SITE_URL = realTargetUrl;

            // صبر کن تا صفحه جدید لود شود
            iframe.onload = function() {
                // بعد از لود، دستگاه را چک کن و اسکرول کن
                if (currentDevice !== comment.device_type) {
                    const targetBtn = document.querySelector(`.device-btn[data-device="${comment.device_type}"]`);
                    if (targetBtn) targetBtn.click();
                }

                setTimeout(() => {
                    let iframeDoc;
                    try {
                        iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    } catch (e) {
                        // cross-origin: اجازه دسترسی به doc نداریم
                        return;
                    }

                    try {
                        const element = iframeDoc.querySelector(comment.selector);
                        if (element) {
                            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            renderPins();
                            const pin = element.querySelector('.rs-pin');
                            if (pin) {
                                showCommentPopup(pin, comment);
                            }
                        }
                    } catch (e) { console.error(e); }
                }, 500);
            };
            return;
        }
        
        // ✅ ۲. اگر URL یکی است (کد قبلی)
        if (currentDevice !== comment.device_type) {
            const targetBtn = document.querySelector(`.device-btn[data-device="${comment.device_type}"]`);
            if (targetBtn) {
                targetBtn.click();
                setTimeout(() => {
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    try {
                        const element = iframeDoc.querySelector(comment.selector);
                        if (element) {
                            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            const pin = element.querySelector('.rs-pin');
                            if (pin) {
                                showCommentPopup(pin, comment);
                            }
                        }
                    } catch (e) { console.error(e); }
                }, 500);
                return;
            }
        }
        
        // اسکرول معمولی
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        try {
            const element = iframeDoc.querySelector(comment.selector);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth', block: 'center' });
                const pin = element.querySelector('.rs-pin');
                if (pin) {
                    showCommentPopup(pin, comment);
                }
            }
        } catch (e) { console.error(e); }
    }


    function showCommentPopup(pinElement, commentData) {
    const iframeDoc = pinElement.ownerDocument;
    
    // حذف پاپ‌آپهای قبلی
    const oldPopups = iframeDoc.querySelectorAll('.rs-comment-popup');
    oldPopups.forEach(p => p.remove());
    
    // ساخت پاپ‌آپ
    const popup = document.createElement('div');
    popup.className = 'rs-comment-popup';
    
    // استایل‌های پایه
    Object.assign(popup.style, {
        position: 'absolute',
        width: '250px',
        backgroundColor: 'white',
        border: '1px solid #ccc',
        borderRadius: '8px',
        boxShadow: '0 4px 15px rgba(0,0,0,0.2)',
        padding: '10px',
        zIndex: '999999',
        fontFamily: 'sans-serif',
        fontSize: '13px',
        color: '#333',
        display: 'block',
        visibility: 'hidden'
    });
    
    // --- تابع کمکی برای فرمت تاریخ ---
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    // --- تابع کمکی برای نمایش ریپلای‌ها ---
    function getRepliesHTML(commentId) {
        const replies = allComments.filter(reply => reply.parent_comment_id === commentId);
        if (replies.length === 0) {
            return '<div style="color: #888; text-align: center; font-size: 12px; padding: 5px 0;">No replies yet</div>';
        }
        return replies.map(reply => `
            <div class="reply-item" style="margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px dashed #eee;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px; align-items: center;">
                    <span style="font-weight: bold; color: #2563eb;">${reply.author_name.split('@')[0]}</span>
                    <span style="font-size: 11px; color: #666;">${formatDate(reply.created_at)}</span>
                </div>
                <div style="line-height: 1.4; white-space: pre-wrap; font-size: 13px; color: #333;">${reply.content}</div>
            </div>
        `).join('');
    }
    
    // --- محتوای داخلی (با ریپلای‌ها) ---
    popup.innerHTML = `
        <div id="mainCommentText" style="margin-bottom: 8px; line-height: 1.4; white-space: pre-wrap;">${commentData.content}</div>

        <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 8px;">
            <div style="display: flex; gap: 8px; align-items: center;">
                ${commentData.author_id === CURRENT_USER_ID ? 
                    `<button class="btn-popup-edit" tabindex="0" style="background: #e0e0e0; color: #333; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">Edit</button>` : 
                    ''}
                ${commentData.author_id === CURRENT_USER_ID ? 
                    `<button class="btn-popup-delete" style="background: #dc2626; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">Delete</button>` : 
                    ''}
                <button class="btn-popup-resolve" style="background: #2563eb; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">Resolve</button>
            </div>
            <button class="btn-popup-close" style="background: none; border: none; color: #666; cursor: pointer; font-size: 16px; font-weight: bold;">×</button>
        </div>                   

        <!-- ورودی ریپلای -->
        <div style="margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
            <div style="display: flex; gap: 8px;">
                <textarea id="replyInput" placeholder="Write a reply..." 
                        style="flex: 1; padding: 4px; border: 0.5px solid #ccc; border-radius: 4px; 
                                font-family: 'Vazir'; font-size: 13px; height: 24px; box-sizing: border-box; resize: none;"></textarea>
                <button id="replyButton" style="background: #2563eb; color: white; border: none; 
                                padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px;">Reply</button>
            </div>
        </div>

        <!-- بخش ریپلای‌ها (جدید) -->
        <div id="repliesContainer" style="max-height: 150px; overflow-y: auto; margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px;">
            ${getRepliesHTML(commentData.id)}
        </div>
        

    `;
    
    // اضافه کردن به body
    iframeDoc.body.appendChild(popup);
    
    // --- محاسبات موقعیت (بدون تغییر) ---
    const popupWidth = popup.offsetWidth;
    const popupHeight = popup.offsetHeight;
    const popupOffset = 5;
    const targetElement = pinElement.parentElement;
    const targetRect = targetElement.getBoundingClientRect();
    const scrollTop = iframeDoc.documentElement.scrollTop || iframeDoc.body.scrollTop;
    const scrollLeft = iframeDoc.documentElement.scrollLeft || iframeDoc.body.scrollLeft;
    const elementDocLeft = targetRect.left + scrollLeft;
    const elementDocTop = targetRect.top + scrollTop;
    const pinLeft = elementDocLeft + parseInt(commentData.offset_x);
    const pinTop = elementDocTop + parseInt(commentData.offset_y);
    let leftPos = pinLeft - (popupWidth / 2);
    
    if (leftPos + popupWidth > iframeDoc.body.scrollWidth) {
        leftPos = pinLeft - popupWidth + 10;
    } else if (leftPos < 0) {
        leftPos = 10;
    }
    let topPos = pinTop - popupHeight - popupOffset;
    
    if (topPos < scrollTop) {
        topPos = pinTop + 20 + popupOffset;
    }
    
    popup.style.left = leftPos + 'px';
    popup.style.top = topPos + 'px';

    // --- افزودن رویداد برای دکمه ریپلای ---
    const replyInput = popup.querySelector('#replyInput');
    const replyButton = popup.querySelector('#replyButton');
    
    replyButton.addEventListener('click', () => {
        const replyText = replyInput.value.trim();
        if (replyText) {
            const formData = new FormData();
            formData.append('siteId', SITE_ID);
            formData.append('url', SITE_URL);
            formData.append('selector', commentData.selector);
            formData.append('deviceType', commentData.device_type);
            formData.append('content', replyText);
            formData.append('offsetX', commentData.offset_x);
            formData.append('offsetY', commentData.offset_y);
            formData.append('parentCommentId', commentData.id);
            
            fetch('save_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    replyInput.value = '';
                    loadComments().then(() => {
                    const repliesContainer = popup.querySelector('#repliesContainer');
                    repliesContainer.innerHTML = getRepliesHTML(commentData.id);
                    });
                } else {
                    alert('Error saving reply: ' + data.message);
                }
            });
        }
    });


    // --- افزودن رویداد برای دکمه Delete ---
    // const deleteButton = popup.querySelector('.btn-popup-delete');
    // if (deleteButton) {
    //     deleteButton.addEventListener('click', () => {
    //         if (confirm('Are you sure you want to delete this comment?')) {
    //             const formData = new FormData();
    //             formData.append('commentId', commentData.id);
                
    //             fetch('delete_comment.php', {
    //                 method: 'POST',
    //                 body: formData
    //             })
    //             .then(res => res.json())
    //             .then(data => {
    //                 if (data.success) {
    //                     popup.remove();
    //                     loadComments();
    //                 } else {
    //                     alert('Error deleting comment: ' + data.message);
    //                 }
    //             });
    //         }
    //     });
    // }
    const deleteButton = popup.querySelector('.btn-popup-delete');
    if (deleteButton) {
        deleteButton.addEventListener('click', () => {
            commentToDeleteId = commentData.id;
            openModal('deleteCommentModal');
        });
    }


    // --- رویدادهای دیگر (بدون تغییر) ---
    popup.querySelector('.btn-popup-close').addEventListener('click', (e) => {
        e.stopPropagation();
        popup.remove();
    });
    popup.querySelector('.btn-popup-resolve').addEventListener('click', (e) => {
        e.stopPropagation();
        toggleResolvedStatus(commentData.id, true);
        popup.remove();
    });
    const editButton = popup.querySelector('.btn-popup-edit');
    if (editButton) {

        // در تابع showCommentPopup (در بخش handleDoneClick)
        // const handleDoneClick = () => {
        // const input = popup.querySelector('textarea');
        // const newText = input.value;
        // const formData = new FormData();
        // formData.append('commentId', commentData.id);
        // formData.append('content', newText);
        
        // fetch('update_comment.php', {
        //     method: 'POST',
        //     body: formData
        // })
        // .then(res => res.json())
        // .then(data => {
        //     if (data.success) {
        //         // ✅ تغییر کلیدی: استفاده از iframeDoc برای ایجاد المان
        //         const newDiv = iframeDoc.createElement('div');
        //         newDiv.textContent = newText;
        //         newDiv.style.marginBottom = '8px';
        //         newDiv.style.lineHeight = '1.4';
        //         newDiv.style.whiteSpace = 'pre-wrap';
        //         newDiv.style.fontFamily = 'inherit';
        //         newDiv.style.fontSize = '13px';
                
        //         input.replaceWith(newDiv);
        //         editButton.textContent = 'Edit';
        //         editButton.classList.remove('btn-popup-done');
        //         editButton.classList.add('btn-popup-edit');
        //         loadComments();
                
        //         // ✅ تغییر کلیدی: استفاده از iframeDoc برای شناسایی دکمه
        //         setTimeout(() => {
        //             const editButton = popup.querySelector('.btn-popup-edit');
        //             if (editButton) {
        //                 editButton.focus();
        //                 console.log('Focus set to Edit button');
        //             }
        //         }, 500);
        //     } else {
        //         alert('Error updating comment');
        //     }
        // });
        // };

        const handleDoneClick = () => {
        const input = popup.querySelector('textarea.comment-edit'); // پیدا کردن تکست‌اریا کلاس دار
        const newText = input.value;
        
        const formData = new FormData();
        formData.append('commentId', commentData.id);
        formData.append('content', newText);
        
        fetch('update_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // ساخت دوباره div متن اصلی با ID اختصاصی
                const newDiv = document.createElement('div');
                newDiv.id = 'mainCommentText'; // ✅ مهم: تنظیم مجدد ID
                newDiv.textContent = newText;
                newDiv.style.marginBottom = '8px';
                newDiv.style.lineHeight = '1.4';
                newDiv.style.whiteSpace = 'pre-wrap';
                newDiv.style.fontFamily = 'inherit';
                newDiv.style.fontSize = '13px';
                
                input.replaceWith(newDiv);
                
                editButton.textContent = 'Edit';
                editButton.classList.remove('btn-popup-done');
                editButton.classList.add('btn-popup-edit');
                
                loadComments(); // رفرش کردن کامنت‌ها
                
                setTimeout(() => {
                    const editBtn = popup.querySelector('.btn-popup-edit');
                    if (editBtn) {
                        editBtn.focus();
                    }
                }, 100);
            } else {
                alert('Error updating comment');
            }
        });
        };
        


        editButton.addEventListener('click', () => {
            // ✅ تغییر: استفاده از ID برای پیدا کردن دقیق متن اصلی
            const commentTextDiv = popup.querySelector('#mainCommentText'); 
            
            const input = document.createElement('textarea');
            input.className = 'comment-edit';
            input.type = 'text';
            input.value = commentTextDiv.textContent;
            input.style.width = '99%';
            input.style.border = '0.5px solid #ccc';
            input.style.padding = '4px';
            input.style.borderRadius = '4px';
            input.style.marginBottom = '4px';
            input.style.fontFamily = 'inherit';
            input.style.fontSize = '13px';
            input.style.height = '80px';
            input.style.boxSizing = 'border-box';
            
            commentTextDiv.replaceWith(input);
            input.focus();
            input.select();
            
            editButton.textContent = 'Done';
            editButton.classList.remove('btn-popup-edit');
            editButton.classList.add('btn-popup-done');
            
            editButton.removeEventListener('click', handleDoneClick);
            editButton.addEventListener('click', handleDoneClick);
        });



    }
    
    // بستن پاپ‌آپ با کلیک بیرون
    iframeDoc.addEventListener('click', function closePopup(event) {
        if (!popup.contains(event.target) && !pinElement.contains(event.target)) {
            popup.remove();
            iframeDoc.removeEventListener('click', closePopup);
        }
    });
}


    // --- 5. Toggle Resolved (Clean Version) ---
    function toggleResolvedStatus(commentId, checkboxState) {
        const valueToSend = checkboxState ? 1 : 0;

        const formData = new FormData();
        formData.append('commentId', commentId);
        formData.append('isResolved', valueToSend);

        fetch('toggle_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // آپدیت حافظه لوکال
                const comment = allComments.find(c => c.id === commentId);
                if (comment) {
                    comment.is_resolved = valueToSend;
                }
                renderSidebar();
                renderPins();
            } else {
                // فقط در صورت خطای واقعی الرت نشان بده
                console.error('Update failed:', data.message);
            }
        })
        .catch(error => {
            console.error('Network error:', error);
        });
    }

    // --- 6. Device Switcher ---
    deviceBtns.forEach(btn => {
        btn.addEventListener('click', () => {

            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
            const popup = iframeDoc.querySelector('.rs-comment-popup');
            if (popup) popup.remove();

            deviceBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const device = btn.dataset.device;
            iframeWrapper.className = `iframe-wrapper ${device}`;
            setTimeout(renderPins, 300);
        });

    });

    // --- 7. Click to Comment (دریافت مختصات دقیق) ---

    iframe.addEventListener('load', () => {
        
        console.log('--- [1] Iframe Loaded. Current Src:', iframe.src);

        // ✅ بخش ۱: استخراج آدرس واقعی و به‌روزرسانی SITE_URL
        try {
            const iframeSrc = iframe.src;
            if (iframeSrc.includes('?url=')) {
                const urlParams = new URLSearchParams(iframeSrc.split('?')[1]);
                const realUrl = urlParams.get('url');
                if (realUrl) {
                    SITE_URL = realUrl;
                    console.log('--- [2] Extracted Real URL for SITE_URL:', SITE_URL);
                    loadComments();
                }
            } else {
                SITE_URL = iframeSrc;
                console.log('--- [2] No Proxy Params. SITE_URL set to:', SITE_URL);
                loadComments();
            }
        } catch (e) {
            console.error('Error parsing URL:', e);
        }
        
        renderPins();
        
        const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        
        if (!iframeDoc) return;
        const newBody = iframeDoc.body;
        
        newBody.addEventListener('click', (e) => {
            const popup = iframeDoc.querySelector('.rs-comment-popup');
            if (popup && popup.contains(e.target)) {
                return; 
            }
            const clickedLink = e.target.closest('a');
            
            if (clickedLink) {
                e.preventDefault();
                e.stopPropagation();
                
                let targetHref = clickedLink.getAttribute('href');
                console.log('--- [3] Link Clicked. Href Attribute:', targetHref);

                if (!targetHref) return;
                
                // ✅ اصلاحیه مهم: بررسی اینکه آیا لینک توسط proxy.php تغییر یافته است
                // اگر لینک شامل proxy.php باشد، باید مثل یک لینک Absolute با آن برخورد شود
                const isProxyLink = targetHref.includes('proxy.php?url=');

                // ✅ بررسی ۱: اگر لینک Absolute بود یا لینک پروکسی شده بود
                if (targetHref.startsWith('http') || targetHref.startsWith('//') || isProxyLink) {
                    console.log('--- [4] Absolute or Proxy Link Detected.');
                    
                    if (isProxyLink) {
                        // اگر لینک پروکسی است، همان را استفاده کن (چون آدرس کامل دارد)
                        console.log('--- [5] Using Proxy Link directly:', targetHref);
                        iframe.src = targetHref;
                    } else {
                        // اگر لینک Absolute معمولی است، آن را پروکسی کن
                        console.log('--- [5] Proxying Absolute Link...');
                        iframe.src = 'proxy.php?url=' + encodeURIComponent(targetHref);
                    }
                    return;
                }
                
                // ✅ بررسی ۲: اگر لینک Relative بود (روش دستی و مطمئن برای لوکال)
                try {
                    try {
                        targetHref = decodeURIComponent(targetHref);
                    } catch (e) {}
                    
                    const currentSrc = iframe.src;
                    console.log('--- [6] Relative Link Detected. Current Iframe Src:', currentSrc);
                    
                    let finalUrl = '';
                    
                    if (targetHref.startsWith('/')) {
                        const basePath = currentSrc.substring(0, currentSrc.indexOf('/', 8));
                        finalUrl = basePath + targetHref;
                        console.log('--- [7] Calculated Absolute (Root):', finalUrl);
                    } else {
                        let lastSlashIndex = currentSrc.lastIndexOf('/');
                        if (lastSlashIndex !== -1) {
                            finalUrl = currentSrc.substring(0, lastSlashIndex + 1) + targetHref;
                        } else {
                            finalUrl = currentSrc + '/' + targetHref;
                        }
                        console.log('--- [7] Calculated Absolute (Relative):', finalUrl);
                    }
                    
                    // ✅ نکته: لینک‌های Relative نباید شامل proxy.php باشند، پس همیشه پروکسی می‌شوند
                    console.log('--- [8] Proxying Relative Link to:', 'proxy.php?url=' + finalUrl);
                    iframe.src = 'proxy.php?url=' + encodeURIComponent(finalUrl);
                    
                } catch (err) {
                    console.error('Error building URL:', err);
                }
                return;
            }
            
            // --- اگر لینک نبود (کد ثبت کامنت) ---
            e.preventDefault();
            
            const rect = e.target.getBoundingClientRect();
            const x = e.clientX; 
            const y = e.clientY;
            
            const clickX = Math.round(x - rect.left);
            const clickY = Math.round(y - rect.top);
            const selector = getCssSelector(e.target);
            const currentDevice = document.querySelector('.device-btn.active').dataset.device;
            
            document.getElementById('targetSelector').value = selector;
            document.getElementById('targetDevice').value = currentDevice;
            
            commentForm.dataset.offsetX = clickX;
            commentForm.dataset.offsetY = clickY;
            
            openModal('commentModal');
        });
    });



    // --- 8. Save Comment (اصلاح شده) ---

    commentForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // ۱. تلاش برای خواندن از دیتا-ست (روش جدید)
        let finalX = commentForm.dataset.offsetX;
        let finalY = commentForm.dataset.offsetY;
        
        // ۲. اگر در دیتا-ست نبود (مثلاً undefined بود)، از متغیرهای سراسری استفاده کن (روش قدیمی)
        // این کار باعث می‌شود اگر به هر دلیلی دیتا-ست پاک شده بود، سیستم کار کند
        if (finalX === undefined || finalY === undefined) {
            finalX = window.clickX;
            finalY = window.clickY;
        }

        // ۳. بررسی نهایی: اگر هنوز هم مقدار نداشت، خطا بده
        if (finalX === undefined || finalY === undefined) {
            alert('لطفاً روی صفحه کلیک کنید تا مختصات ثبت شود.');
            return;
        }

        const formData = new FormData(commentForm);
        formData.append('siteId', SITE_ID);
        formData.append('url', SITE_URL);
        
        // ✅ نکته مهم: تبدیل قطعی به عدد (Integer)
        // اگر مقدار رشته بود، عدد می‌شود. اگر null بود، 0 می‌شود.
        formData.append('offsetX', parseInt(finalX) || 0);
        formData.append('offsetY', parseInt(finalY) || 0);
        
        fetch('save_comment.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                closeModal('commentModal');
                document.getElementById('commentText').value = '';
                
                // پاکسازی مقادیر
                delete commentForm.dataset.offsetX;
                delete commentForm.dataset.offsetY;
                window.clickX = undefined;
                window.clickY = undefined;
                
                loadComments();
            } else {
                alert('Error saving comment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while saving.');
        });
    });

    // Helper: Generate CSS Selector
    function getCssSelector(element) {
        if (element.id) return '#' + element.id;
        let path = [];
        while (element.nodeType === Node.ELEMENT_NODE) {
            let selector = element.nodeName.toLowerCase();
            if (element.id) {
                selector += '#' + element.id;
                path.unshift(selector);
                break;
            } else {
                if (element.className) {
                    selector += '.' + element.className.split(' ').join('.');
                }
                path.unshift(selector);
                element = element.parentNode;
            }
        }
        return path.join(' > ');
    }

    // Modal Helpers
    window.openModal = (id) => document.getElementById(id).style.display = 'block';
    window.closeModal = (id) => document.getElementById(id).style.display = 'none';
    document.querySelector('.close-modal').addEventListener('click', () => closeModal('commentModal'));


    const deleteCommentModal = document.getElementById('deleteCommentModal');
    const confirmCommentDeleteBtn = document.getElementById('confirmCommentDeleteBtn');
    const cancelCommentDeleteBtn = document.getElementById('cancelCommentDeleteBtn');
    const deleteCommentModalClose = deleteCommentModal.querySelector('.close');

    // رویداد برای دکمه تأیید حذف
    confirmCommentDeleteBtn.addEventListener('click', () => {
        if (commentToDeleteId) {
            const formData = new FormData();
            formData.append('commentId', commentToDeleteId);
            
            fetch('delete_comment.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // بستن پاپ‌آپ کامنت اگر باز است
                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    const popup = iframeDoc.querySelector('.rs-comment-popup');
                    if (popup) popup.remove();
                    
                    closeModal('deleteCommentModal');
                    loadComments();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while deleting the comment.');
            });
        }
    });

    // رویداد برای دکمه لغو
    cancelCommentDeleteBtn.addEventListener('click', () => {
        closeModal('deleteCommentModal');
        commentToDeleteId = null;
    });

    // رویداد برای دکمه بستن (X)
    deleteCommentModalClose.addEventListener('click', () => {
        closeModal('deleteCommentModal');
        commentToDeleteId = null;
    });


});
