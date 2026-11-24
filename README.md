# TaskFlow

TaskFlow تطبيق ويب جامعي لإدارة المهام. الواجهة بالإنجليزي والشرح بالعربي. المشروع مقسوم إلى تطبيق PHP ومشروع اختبارات Java مستقل داخل `selenium/`.

## ١. جهّز الأدوات

- نزّل VS Code وافتح فيه مجلد `TaskFlow`.
- نزّل XAMPP بإصدار فيه PHP 8.1 أو أحدث. قواعد البيانات في Supabase، فمش محتاج تشغّل MySQL في XAMPP.
- على Windows افتح PowerShell من مجلد المشروع. نفّذ `& 'C:\xampp\php\php.exe' -v` للتأكد إن PHP شغّال. لو مسار التثبيت مختلف، عدّل المسار.
- نفّذ `& 'C:\xampp\php\php.exe' -m` وتأكد إن القائمة فيها `curl`. لو ناقصة، افتح `C:\xampp\php\php.ini` وشيل علامة التعليق من سطر `extension=curl` وبعدها أعد تشغيل السيرفر.
- على Linux أو macOS استخدم `php -v` و`php -m` لو PHP متاح من الطرفية.

## ٢. شغّل الواجهة لأول مرة

من داخل مجلد `TaskFlow` نفّذ:

~~~powershell
& 'C:\xampp\php\php.exe' -S 127.0.0.1:8000 -t public
~~~

لو PHP موجود في PATH:

~~~sh
php -S 127.0.0.1:8000 -t public
~~~

افتح `http://127.0.0.1:8000/` في Chrome. اختبر فتح صفحة التسجيل وارجع لتسجيل الدخول. جرّب نافذة بأبعاد موبايل من أدوات المطوّر. تشغيل PHP بهذه الطريقة يستخدم PHP بتاع XAMPP مباشرة. عند استخدام Apache اجعل `public/` فقط هو DocumentRoot. السيرفر المدمج مناسب للتطوير المحلي. عند النشر استخدم HTTPS وخادم PHP مخصص للإنتاج.

## ٣. أنشئ قاعدة البيانات

1. سجّل الدخول إلى [Supabase](https://supabase.com/dashboard) واختر **New project**. احتفظ بكلمة مرور قاعدة البيانات في مكان آمن.
2. افتح **SQL Editor** ثم **New query**. انسخ محتوى `database/schema.sql` بالكامل واضغط **Run** مرة واحدة في المشروع الجديد.
3. من **Table Editor** تأكد إن جدول `tasks` ظاهر، ومن إعدادات الجدول تأكد إن RLS مفعّلة.
4. من **Authentication → Providers → Email** تأكد إن تسجيل الدخول بالإيميل مفعّل. خليك على تفعيل تأكيد الإيميل للتشغيل العادي.
5. من **Authentication → URL Configuration** اضبط **Site URL** على `http://127.0.0.1:8000` للتطوير. لو نشرت التطبيق غيّر القيمة لرابط الموقع الفعلي. تأكيد الرسالة يفعّل الحساب، وبعده المستخدم يسجّل الدخول من جديد. استخدم SMTP مناسب أو عناوين يسمح بها بريد مشروع Supabase لتجربة التأكيد.

## ٤. اربط PHP بـ Supabase

1. انسخ `config/local.example.php` باسم `config/local.override.php` في نفس المجلد.
2. من **Connect** في لوحة Supabase انسخ Project URL مثل `https://your-project-ref.supabase.co`.
3. من **Settings → API Keys** انسخ **Publishable key** اللي يبدأ بـ `sb_publishable_`. عدّل قيمتي `url` و`key` في `config/local.override.php`. ممكن كمان تستخدم متغيري البيئة `SUPABASE_URL` و`SUPABASE_PUBLISHABLE_KEY`.
لو نسختك الحالية فيها بالفعل إعدادات حقيقية في `config/local.php`، احتفظ بها محليًا. قبل تنزيل أو دمج نسخة أحدث، انقل القيم إلى `config/local.override.php` حتى تقدر تستخدم ملف الإعدادات الافتراضي الموجود على GitHub.

4. افتح صفحة التسجيل وأنشئ مستخدم اختبار. بعد تأكيد الإيميل سجّل الدخول. راجع المستخدم داخل **Authentication → Users**.
5. أضف مهمة. راجعها داخل **Table Editor → tasks**. جرّب تعديل العنوان، تغيير الحالة، الحذف، وتسجيل الخروج. راجع إن العداد بيتغيّر بعد كل عملية.

استخدم مفتاح Publishable مع JWT الخاص بالمستخدم. صلاحية عمليات المهام جاية من جلسة المستخدم وسياسات RLS. المشروع لا يحتاج Service Role Key أو كلمة مرور قاعدة البيانات للاتصال.

## ٥. اتبع مسار الكود

| الملف | دوره وأهم جزء فيه |
| --- | --- |
| `public/index.php` | يحدد وضع تسجيل الدخول ويعرض النموذج المشترك. |
| `public/register.php` | يحدد وضع إنشاء حساب ويعرض النموذج نفسه مع حقول إضافية. |
| `views/auth-form.php` | يرسل البيانات بطريقة POST ويعرض الرسالة الناتجة بعد إعادة التوجيه. |
| `views/layout.php` | يجهز HTML العام وتحميل CSS وJavaScript. |
| `public/dashboard.php` | يتأكد من الجلسة، يقرأ صفحات المهام، ويلخص الحالات في أربع بطاقات. |
| `public/task.php` | يعرض نموذجًا فارغًا للإضافة، أو يحمّل المهمة المطلوبة للتعديل. |
| `public/api/auth.php` | يتحقق من الحقول ويرسلها إلى Supabase Auth. بعد الدخول يغيّر رقم جلسة PHP. |
| `public/api/tasks.php` | يتحقق من البيانات ويحدد عملية REST المطلوبة حسب زر النموذج. |
| `public/logout.php` | يطلب إلغاء جلسة Supabase ويمسح بيانات الدخول من جلسة PHP. |
| `config/bootstrap.php` | يدير الجلسة وCSRF ورسائل النماذج وتجديد الـ JWT ومعالجة نتائج المهام. |
| `config/supabase.php` | يستخدم cURL للاتصال بواجهة Supabase عبر HTTPS وتمرير مفتاح المشروع وJWT عند الحاجة. |
| `config/local.example.php` | نموذج لإنشاء ملف إعداد الاتصال المحلي. |
| `config/local.php` | ملف الإعداد الافتراضي بقيم توضيحية. `config/local.override.php` يضبط القيم الفعلية على جهازك. |
| `public/css/style.css` | يحدد الألوان والتخطيط وحجم البطاقات على الكمبيوتر والموبايل. |
| `public/favicon.ico` | أيقونة TaskFlow في تبويب المتصفح. |
| `public/js/script.js` | يطابق حقلي كلمة المرور، ويؤكد الحذف، ويرسل تغيير الحالة. PHP يعيد التحقق من النماذج. |
| `database/schema.sql` | ينشئ الجدول والفهرس والصلاحيات وسياسات عزل المهام. |
| `.gitignore` | يستبعد إعداد الاتصال المحلي وملفات Maven الناتجة. |
| `selenium/pom.xml` | يثبت إصدارات Selenium وJUnit 5 ويضبط تجميع Java وتشغيل الاختبارات. |
| `selenium/src/test/java/taskflow/BrowserTest.java` | يفتح Chrome ويغلقه لكل اختبار ويوفر خطوات الدخول والانتظار المشتركة. |
| `LoginTest.java` و`RegisterTest.java` | يختبران الدخول، حماية اللوحة، والتحقق من نماذج الحساب. |
| `TaskManagementTest.java` و`LogoutTest.java` | يختبران دورة حياة المهمة والخروج من الحساب. |

### مثال تتبّع إضافة مهمة

افتح صفحة **New task**. بعد الضغط على **Add task** النموذج يرسل العنوان والوصف والحالة والتاريخ وCSRF إلى `api/tasks.php`. PHP يتحقق من الطلب والجلسة والحقول، ثم يرسل طلب POST إلى PostgREST مع JWT. قاعدة البيانات تستخرج `user_id` من `auth.uid()` وتطبّق سياسة الإدراج. PHP يعيد توجيه المتصفح إلى اللوحة، واللوحة تقرأ بيانات المهام من جديد.

حساب المستخدم موجود في `auth.users`، والاسم محفوظ في بيانات Supabase Auth. جدول `profiles` اختياري في التصميم، وبيانات الحساب الحالية تكفي لعرض الاسم. علاقة المستخدم بالمهام واحد إلى متعدد. قاعدة البيانات تنشئ UUID للمهمة وتحفظ تاريخ الإنشاء. صلاحيات التعديل محدودة بأعمدة المهمة، وسياسات القراءة والإضافة والتعديل والحذف تربط كل صف بصاحبه.

## ٦. اختبر التطبيق

ابدأ بملف `docs/manual-tests.md`. شغّل الاختبارات اليدوية على حسابين مخصصين للتجربة وسجّل النتائج الفعلية. بعدها ثبّت JDK 17 أو أحدث وMaven وChrome. تأكد باستخدام `java -version` و`mvn -version`.

في PowerShell، بعد تأكيد حساب الاختبار وتشغيل PHP:

~~~powershell
$env:TASKFLOW_TEST_EMAIL = 'your-confirmed-test-account@example.com'
$env:TASKFLOW_TEST_PASSWORD = 'your-test-password'
cd selenium
mvn test
~~~

على Bash استخدم `export TASKFLOW_TEST_EMAIL='…'` و`export TASKFLOW_TEST_PASSWORD='…'` قبل `mvn test`. خيارات التشغيل تشمل `-Dtaskflow.url=http://127.0.0.1:8000` و`-Dtaskflow.headless=true`. Selenium Manager يدير ChromeDriver تلقائيًا عادةً. لو احتجت تحدد المسار استخدم `-Dwebdriver.chrome.driver=PATH`. الاختبارات اللي تعتمد على حساب مؤكد تُسجّل **Skipped** لو بياناته مش موجودة. تقارير JUnit في `selenium/target/surefire-reports/`.

## ٧. جهّز المناقشة

استخدم `docs/demo.md` لترتيب العرض، و`docs/verification.md` لمراجعة الفحوصات المنفذة والفحوصات المرتبطة بمشروع Supabase الفعلي.
