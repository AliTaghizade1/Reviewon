<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lang = 'en';
if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'fa'], true)) {
    $lang = $_GET['lang'];
} elseif (isset($_SESSION['lang']) && in_array($_SESSION['lang'], ['en', 'fa'], true)) {
    $lang = $_SESSION['lang'];
} elseif (isset($_COOKIE['reviewon_lang']) && in_array($_COOKIE['reviewon_lang'], ['en', 'fa'], true)) {
    $lang = $_COOKIE['reviewon_lang'];
}

if (!in_array($lang, ['en', 'fa'], true)) {
    $lang = 'en';
}

$_SESSION['lang'] = $lang;
setcookie('reviewon_lang', $lang, time() + 60 * 60 * 24 * 365, '/', '', false, true);
$dir = $lang === 'fa' ? 'rtl' : 'ltr';

$translations = [
    'nav_features' => ['en' => 'Features', 'fa' => 'ویژگی‌ها'],
    'nav_login' => ['en' => 'Login', 'fa' => 'ورود'],
    'nav_home' => ['en' => 'Back to home', 'fa' => 'بازگشت به صفحه اصلی'],
    'nav_dashboard' => ['en' => 'Dashboard', 'fa' => 'داشبورد'],
    'nav_profile' => ['en' => 'Profile', 'fa' => 'پروفایل'],
    'nav_logout' => ['en' => 'Logout', 'fa' => 'خروج'],
    'nav_toggle' => ['en' => 'فارسی', 'fa' => 'English'],
    'nav_toggle_label' => ['en' => 'Switch to Persian', 'fa' => 'تغییر به انگلیسی'],
    'auth_welcome' => ['en' => 'Welcome', 'fa' => 'خوش آمدید'],
    'auth_login_label' => ['en' => 'Email', 'fa' => 'ایمیل'],
    'auth_password_label' => ['en' => 'Password', 'fa' => 'رمز عبور'],
    'auth_login_submit' => ['en' => 'Login / Sign Up', 'fa' => 'ورود / ثبت‌نام'],
    'auth_hint' => ['en' => 'No account? One will be created automatically.', 'fa' => 'حساب کاربری ندارید؟ به‌صورت خودکار ساخته می‌شود.'],
    'auth_forgot_title' => ['en' => 'Forgot Password', 'fa' => 'فراموشی رمز عبور'],
    'auth_forgot_submit' => ['en' => 'Send reset link', 'fa' => 'ارسال لینک بازنشانی'],
    'auth_forgot_back' => ['en' => 'Back to login', 'fa' => 'بازگشت به ورود'],
    'auth_placeholder_email' => ['en' => 'your@email.com', 'fa' => 'your@email.com'],
    'auth_placeholder_password' => ['en' => 'Enter your password', 'fa' => 'رمز عبور خود را وارد کنید'],
    'footer_copyright' => ['en' => '© 2024 Reviewon. All rights reserved.', 'fa' => '© 2024 Reviewon. همه حقوق محفوظ است.'],
    'footer_privacy' => ['en' => 'Privacy', 'fa' => 'حریم خصوصی'],
    'footer_terms' => ['en' => 'Terms', 'fa' => 'شرایط استفاده'],
    'footer_contact' => ['en' => 'Contact', 'fa' => 'تماس'],
    'hero_title' => ['en' => 'Clear Feedback,<br>Faster Projects.', 'fa' => 'بازخورد روشن،<br>پروژه‌های سریع‌تر.'],
    'hero_subtitle' => ['en' => 'A feedback management platform for collecting and organizing website & feedback to move projects forward faster and more clearly.', 'fa' => 'پلتفرمی برای جمع‌آوری و سازمان‌دهی بازخوردهای وب‌سایت و پروژه‌ها برای پیشبرد سریع‌تر و واضح‌تر کارها.'],
    'hero_stat_1_label' => ['en' => 'Feedbacks Collected', 'fa' => 'بازخورد جمع‌آوری‌شده'],
    'hero_stat_2_label' => ['en' => 'Active Projects', 'fa' => 'پروژه‌های فعال'],
    'hero_stat_3_label' => ['en' => 'Happy Users', 'fa' => 'کاربران راضی'],
    'hero_cta' => ['en' => 'Get Started - Free', 'fa' => 'شروع رایگان'],
    'features_title' => ['en' => 'Key Features', 'fa' => 'ویژگی‌های کلیدی'],
    'feature_1_title' => ['en' => 'Instant Feedback', 'fa' => 'بازخورد لحظه‌ای'],
    'feature_1_desc' => ['en' => 'Click and feedback appears instantly. No delay, no complexity.', 'fa' => 'روی هر بخش کلیک کنید و بازخورد بلافاصله دیده می‌شود. بدون تأخیر و بدون پیچیدگی.'],
    'feature_2_title' => ['en' => 'Fully Responsive', 'fa' => 'کاملاً ریسپانسیو'],
    'feature_2_desc' => ['en' => 'Mobile, tablet, desktop. Feedback anywhere.', 'fa' => 'موبایل، تبلت و دسکتاپ. بازخورد در هر جا.'],
    'feature_3_title' => ['en' => 'Team Collaboration', 'fa' => 'همکاری تیمی'],
    'feature_3_desc' => ['en' => 'Easy sharing & team work on feedback.', 'fa' => 'اشتراک‌گذاری و همکاری تیمی روی بازخوردها به‌سادگی.'],
    'feature_4_title' => ['en' => 'Secure & Private', 'fa' => 'امن و محرمانه'],
    'feature_4_desc' => ['en' => 'Your data fully secure and confidential.', 'fa' => 'اطلاعات شما کاملاً امن و محرمانه است.'],
    'site_title' => ['en' => 'Reviewon - Professional Feedback Platform', 'fa' => 'Reviewon - پلتفرم بازخورد حرفه‌ای'],
    'privacy_title' => ['en' => 'Privacy Policy', 'fa' => 'سیاست حریم خصوصی'],
    'terms_title' => ['en' => 'Terms of Service', 'fa' => 'شرایط استفاده از خدمات'],
    'reset_title' => ['en' => 'Set New Password', 'fa' => 'تنظیم رمز عبور جدید'],
    'reset_confirm' => ['en' => 'Confirm', 'fa' => 'تأیید'],
    'dashboard_title' => ['en' => 'Dashboard', 'fa' => 'داشبورد'],
    'dashboard_search_placeholder' => ['en' => 'Search...', 'fa' => 'جستجو...'],
    'dashboard_new_site' => ['en' => 'New Site', 'fa' => 'سایت جدید'],
    'dashboard_no_sites' => ['en' => 'No sites yet. Click "New Site" to start.', 'fa' => 'هنوز سایتی وجود ندارد. برای شروع روی «سایت جدید» کلیک کنید.'],
    'dashboard_open' => ['en' => 'Open', 'fa' => 'باز کردن'],
    'dashboard_share' => ['en' => 'Share Access', 'fa' => 'اشتراک‌گذاری دسترسی'],
    'dashboard_delete' => ['en' => 'Delete', 'fa' => 'حذف'],
    'dashboard_share_modal_title' => ['en' => 'Share Access', 'fa' => 'اشتراک‌گذاری دسترسی'],
    'dashboard_share_email_label' => ['en' => 'Add Collaborator (Email)', 'fa' => 'افزودن همکاری‌کننده (ایمیل)'],
    'dashboard_share_email_placeholder' => ['en' => 'colleague@example.com', 'fa' => 'colleague@example.com'],
    'dashboard_share_button' => ['en' => 'Grant Access', 'fa' => 'اعطای دسترسی'],
    'dashboard_access_list_title' => ['en' => 'People with access', 'fa' => 'افراد دارای دسترسی'],
    'dashboard_access_loading' => ['en' => 'Loading...', 'fa' => 'در حال بارگذاری...'],
    'dashboard_add_site_title' => ['en' => 'Add New Site', 'fa' => 'افزودن سایت جدید'],
    'dashboard_site_url_label' => ['en' => 'Website URL', 'fa' => 'آدرس وب‌سایت'],
    'dashboard_site_url_placeholder' => ['en' => 'example.com', 'fa' => 'example.com'],
    'dashboard_create_site' => ['en' => 'Create', 'fa' => 'ایجاد'],
    'dashboard_delete_confirm_title' => ['en' => 'Delete Site?', 'fa' => 'حذف سایت؟'],
    'dashboard_delete_confirm_text' => ['en' => 'Are you sure you want to delete this site? All comments and access permissions will be permanently removed.', 'fa' => 'آیا مطمئن هستید که می‌خواهید این سایت را حذف کنید؟ همه نظرات و مجوزهای دسترسی برای همیشه حذف خواهند شد.'],
    'dashboard_cancel' => ['en' => 'Cancel', 'fa' => 'لغو'],
    'dashboard_confirm_delete' => ['en' => 'Yes, Delete', 'fa' => 'بله، حذف شود'],
    'account_title' => ['en' => 'Account', 'fa' => 'حساب کاربری'],
    'account_personal_info' => ['en' => 'Personal information', 'fa' => 'اطلاعات شخصی'],
    'account_full_name_label' => ['en' => 'Full name', 'fa' => 'نام کامل'],
    'account_save_name' => ['en' => 'Save name', 'fa' => 'ذخیره نام'],
    'account_change_password' => ['en' => 'Change password', 'fa' => 'تغییر رمز عبور'],
    'account_current_password' => ['en' => 'Current password', 'fa' => 'رمز عبور فعلی'],
    'account_new_password' => ['en' => 'New password', 'fa' => 'رمز عبور جدید'],
    'account_confirm_password' => ['en' => 'Confirm new password', 'fa' => 'تأیید رمز عبور جدید'],
    'account_save_password' => ['en' => 'Save password', 'fa' => 'ذخیره رمز عبور'],
    'account_plans_title' => ['en' => 'Plans', 'fa' => 'پلن‌ها'],
    'account_plan_text' => ['en' => 'Do you want to increase your account\'s capabilities?', 'fa' => 'آیا می‌خواهید قابلیت‌های حساب کاربری خود را افزایش دهید؟'],
    'account_plan_message' => ['en' => 'Send us a message at', 'fa' => 'برای ما پیام بفرستید در'],
    'privacy_1_title' => ['en' => '1. Information We Collect', 'fa' => '1. اطلاعاتی که جمع‌آوری می‌کنیم'],
    'privacy_1_text' => ['en' => '• User account details (email, password, and any profile information).<br>• Comments, feedback, and any uploaded content.<br>• Usage data such as IP address, device type, and browser details for analytical purposes.', 'fa' => '• جزئیات حساب کاربری (ایمیل، رمز عبور و هرگونه اطلاعات پروفایل).<br>• نظرات، بازخورد و هر محتوای بارگذاری‌شده.<br>• داده‌های استفاده مانند آدرس IP، نوع دستگاه و جزئیات مرورگر برای اهداف تحلیلی.'],
    'privacy_2_title' => ['en' => '2. How We Use Information', 'fa' => '2. نحوه استفاده از اطلاعات'],
    'privacy_2_text' => ['en' => 'The data is used to provide, maintain, and improve the service, personalize content, and communicate with users. We may also use it to comply with legal obligations or to enforce our Terms of Service.', 'fa' => 'این داده‌ها برای ارائه، نگهداری و بهبود سرویس، شخصی‌سازی محتوا و ارتباط با کاربران استفاده می‌شوند. همچنین ممکن است برای رعایت تعهدات قانونی یا اجرای شرایط خدمات استفاده شوند.'],
    'privacy_3_title' => ['en' => '3. Data Sharing', 'fa' => '3. به اشتراک‌گذاری داده‌ها'],
    'privacy_3_text' => ['en' => 'We do not sell or rent personal data to third parties. We may share aggregated, anonymized data for analytics or with service partners only for the purpose of providing the service. Any data shared with collaborators remains protected under our privacy safeguards.', 'fa' => 'ما داده‌های شخصی را به اشخاص ثالث نمی‌فروشیم یا اجاره نمی‌دهیم. ممکن است داده‌های خلاصه‌شده و ناشناس را برای تحلیل یا با شرکای خدماتی فقط برای ارائه خدمات به اشتراک بگذاریم. هر داده‌ای که با همکاران به اشتراک گذاشته شود، تحت حفاظت‌های حریم خصوصی ما باقی می‌ماند.'],
    'privacy_4_title' => ['en' => '4. Cookies and Tracking', 'fa' => '4. کوکی‌ها و ردیابی'],
    'privacy_4_text' => ['en' => 'Reviewon uses cookies and similar tracking technologies to enhance user experience, analyze traffic, and personalize content. Users can manage cookie preferences via browser settings or by disabling cookies in the site settings.', 'fa' => 'Reviewon از کوکی‌ها و فناوری‌های مشابه برای بهبود تجربه کاربری، تحلیل ترافیک و شخصی‌سازی محتوا استفاده می‌کند. کاربران می‌توانند تنظیمات کوکی را از طریق تنظیمات مرورگر یا غیرفعال‌سازی کوکی‌ها در تنظیمات سایت مدیریت کنند.'],
    'privacy_5_title' => ['en' => '5. Security', 'fa' => '5. امنیت'],
    'privacy_5_text' => ['en' => 'We employ reasonable technical and organizational measures to protect user data from unauthorized access, disclosure, alteration, or destruction. However, no system can guarantee absolute security.', 'fa' => 'ما اقدامات فنی و سازمانی معقولی برای محافظت از داده‌های کاربر در برابر دسترسی، افشای، تغییر یا از بین رفتن غیرمجاز به کار می‌بریم. با این حال، هیچ سامانه‌ای نمی‌تواند امنیت مطلق را تضمین کند.'],
    'privacy_6_title' => ['en' => '6. Data Retention', 'fa' => '6. نگهداری داده‌ها'],
    'privacy_6_text' => ['en' => 'Personal data is retained only as long as necessary to provide the service or comply with legal obligations. After that, we delete or anonymize the data securely.', 'fa' => 'داده‌های شخصی فقط تا زمانی که برای ارائه خدمات یا رعایت تعهدات قانونی لازم باشد، نگهداری می‌شوند. پس از آن، داده‌ها را به‌صورت ایمن حذف یا ناشناس می‌کنیم.'],
    'privacy_7_title' => ['en' => '7. Your Rights', 'fa' => '7. حقوق شما'],
    'privacy_7_text' => ['en' => 'Users can request to access, correct, delete, or export their personal information. To exercise any of these rights, please contact our support team.', 'fa' => 'کاربران می‌توانند برای دسترسی، اصلاح، حذف یا صادرات اطلاعات شخصی خود درخواست ارسال کنند. برای اعمال هر یک از این حقوق، لطفاً با تیم پشتیبانی ما تماس بگیرید.'],
    'privacy_8_title' => ['en' => '8. Changes to the Privacy Policy', 'fa' => '8. تغییرات در سیاست حریم خصوصی'],
    'privacy_8_text' => ['en' => 'We may modify this privacy policy from time to time. Updated versions will be posted on this page and are effective immediately. Continued use of the site after changes constitutes acceptance of the new policy.', 'fa' => 'ما ممکن است این سیاست حریم خصوصی را از زمان به زمان تغییر دهیم. نسخه‌های به‌روزرسانی‌شده در این صفحه منتشر می‌شوند و بلافاصله اعمال می‌شوند. استفاده مداوم از سایت پس از تغییرات، پذیرش سیاست جدید را نشان می‌دهد.'],
    'terms_1_title' => ['en' => '1. Acceptance of Terms', 'fa' => '1. پذیرش شرایط'],
    'terms_1_text' => ['en' => 'By using Reviewon, you agree to comply with and be bound by these Terms of Service. If you do not agree with any part of these terms, you may not use the site.', 'fa' => 'با استفاده از Reviewon، شما موافقت می‌کنید که این شرایط خدمات را رعایت کنید و ملزم به آن باشید. اگر با هر بخشی از این شرایط موافق نیستید، استفاده از سایت مجاز نیست.'],
    'terms_2_title' => ['en' => '2. User Responsibilities', 'fa' => '2. مسئولیت‌های کاربر'],
    'terms_2_text' => ['en' => 'You are responsible for all activity that occurs under your account. Keep your login information secure and notify us immediately of any unauthorized use.', 'fa' => 'شما مسئول همه فعالیت‌هایی هستید که در زیر حساب کاربری شما انجام می‌شود. اطلاعات ورود خود را ایمن نگه دارید و در صورت استفاده غیرمجاز فوراً به ما اطلاع دهید.'],
    'terms_3_title' => ['en' => '3. Content', 'fa' => '3. محتوا'],
    'terms_3_text' => ['en' => 'Users may submit comments and feedback. We reserve the right to review, edit, or remove any content that violates our policies or is illegal. Content you provide remains yours, but you grant us a non‑exclusive license to use it within the scope of the service.', 'fa' => 'کاربران می‌توانند نظرات و بازخورد ارسال کنند. ما حق بررسی، ویرایش یا حذف هر محتوایی را که با سیاست‌های ما مغایرت دارد یا غیرقانونی است، برای خود محفوظ می‌داریم. محتوایی که ارائه می‌کنید، متعلق به شما می‌ماند، اما مجوزی غیرانحصاری به ما می‌دهید تا آن را در محدوده سرویس استفاده کنیم.'],
    'terms_4_title' => ['en' => '4. Prohibited Conduct', 'fa' => '4. رفتارهای ممنوع'],
    'terms_4_text' => ['en' => 'Harassment, hate speech, defamatory statements, or any illegal activity is not allowed. We may suspend or terminate accounts that violate these rules.', 'fa' => 'آزار و اذیت، گفتار نفرت‌انگیز، ادعاهای توهین‌آمیز یا هر فعالیت غیرقانونی مجاز نیست. در صورت نقض این قوانین، ممکن است حساب‌های کاربری را تعلیق یا خاتمه دهیم.'],
    'terms_5_title' => ['en' => '5. Modifications', 'fa' => '5. تغییرات'],
    'terms_5_text' => ['en' => 'We may update these terms at any time. Changes will be posted on this page and become effective immediately upon posting. Your continued use of the site constitutes acceptance of the new terms.', 'fa' => 'ما ممکن است این شرایط را در هر زمان به‌روزرسانی کنیم. تغییرات در این صفحه منتشر می‌شوند و بلافاصله پس از انتشار اعمال می‌شوند. استفاده مداوم شما از سایت، پذیرش شرایط جدید را نشان می‌دهد.'],
    'terms_6_title' => ['en' => '6. Limitation of Liability', 'fa' => '6. محدودیت مسئولیت'],
    'terms_6_text' => ['en' => 'Reviewon is provided “as is.” We do not guarantee the accuracy, reliability, or completeness of any content. We are not liable for any indirect, incidental, or consequential damages arising from your use of the service.', 'fa' => 'Reviewon «به‌صورت فعلی» ارائه می‌شود. ما دقت، قابلیت اطمینان یا کامل بودن هیچ محتوایی را تضمین نمی‌کنیم. ما مسئول هیچ خسارت غیرمستقیم، اتفاقی یا تبعی ناشی از استفاده شما از سرویس نیستیم.'],
    'terms_7_title' => ['en' => '7. Governing Law', 'fa' => '7. قانون حاکم'],
    'terms_7_text' => ['en' => 'These terms shall be governed by the laws of the jurisdiction where our headquarters are located, without regard to its conflict of law principles.', 'fa' => 'این شرایط بر اساس قوانین حوزه‌ای که دفتر اصلی ما در آن قرار دارد، بدون توجه به اصول تعارض قوانین، حاکم خواهد بود.'],
];

function t($key, $currentLang = null) {
    global $translations;
    global $lang;
    $activeLang = $currentLang ?? $lang;
    return $translations[$key][$activeLang] ?? $translations[$key]['en'] ?? $key;
}

function build_lang_switch_url() {
    global $lang;
    $targetLang = $lang === 'en' ? 'fa' : 'en';
    $queryParams = $_GET;
    unset($queryParams['lang']);
    $queryParams['lang'] = $targetLang;
    $query = http_build_query($queryParams);
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: ($_SERVER['SCRIPT_NAME'] ?? 'index.php');
    return $path . ($query ? '?' . $query : '');
}
