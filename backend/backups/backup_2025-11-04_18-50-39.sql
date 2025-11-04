-- Shenava Database Backup
-- Generated: 2025-11-04 18:50:39
-- Database: shenava_db

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `app_settings`;
CREATE TABLE `app_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_persian_ci,
  `setting_type` enum('string','integer','boolean','json') COLLATE utf8mb4_persian_ci DEFAULT 'string',
  `description` text COLLATE utf8mb4_persian_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `app_settings`
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('1', 'app_name', 'شنوا', 'string', NULL, '2025-11-04 20:24:19', '2025-11-04 20:24:19');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('2', 'app_description', 'نرم افزار شنوا مرجع انواع پادکست ها و کتاب صوتی های رایگان', 'string', NULL, '2025-11-04 20:24:19', '2025-11-04 20:24:19');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('3', 'contact_email', 'namayandeshayan@gmail.com', 'string', NULL, '2025-11-04 20:24:19', '2025-11-04 20:24:19');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('4', 'items_per_page', '20', 'string', NULL, '2025-11-04 20:24:19', '2025-11-04 20:24:19');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('5', 'enable_registration', '1', 'string', NULL, '2025-11-04 20:24:19', '2025-11-04 20:24:19');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('6', 'maintenance_mode', '0', 'string', NULL, '2025-11-04 20:24:19', '2025-11-04 20:24:19');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('7', 'storage_audio_path', '/assets/audio/', 'string', NULL, '2025-11-04 20:24:26', '2025-11-04 20:24:26');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('8', 'storage_images_path', '/assets/images/', 'string', NULL, '2025-11-04 20:24:26', '2025-11-04 20:24:26');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('9', 'storage_max_file_size', '50', 'string', NULL, '2025-11-04 20:24:26', '2025-11-04 20:24:26');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('10', 'storage_allowed_audio_types', 'mp3,wav,m4a,ogg', 'string', NULL, '2025-11-04 20:24:26', '2025-11-04 20:24:26');
INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES ('11', 'storage_allowed_image_types', 'jpg,jpeg,png,gif,webp,svg', 'string', NULL, '2025-11-04 20:24:26', '2025-11-04 20:24:26');

DROP TABLE IF EXISTS `authors`;
CREATE TABLE `authors` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_persian_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `bio` text COLLATE utf8mb4_persian_ci,
  `avatar_url` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `website_url` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `authors`
INSERT INTO `authors` (`id`, `uuid`, `name`, `bio`, `avatar_url`, `website_url`, `is_active`, `created_at`) VALUES ('1', '270307c7-7d06-4e5b-a710-7c49de7df334', 'جی کی رولینگ', 'جی.کی رولینگ کیست؟
جی. کی رولینگ- با نام اصلی‌ جوآن رولینگ- را کمتر کسی است که نشناسد، او خالق مجموعه رمان‌های هری پاتر است. او در سال 1965 در بریتانیا متولد شد. جی کی رولینگ شهرتش را با نوشتن مجموعه هری پاتر به دست آورد. کتاب‌هایی که به بیش از 65 زبان دنیا ترجمه‌شده و به‌طور تقریبی در 450 میلیون نسخه منتشرشده است. مجموعه داستان‌های هری پاتر یکی از پرفروش‌ترین کتاب‌های داستانی جهان بوده است به طوری که در سال 2007 ساندی تایمز در فهرست ثروتمندان جهان ثروت رولینگ را حدود ۱٫۰۷ میلیارد دلار تخمین زد و در بین پولدارترین زنان انگلیس او را در رده 13 جدول قرار دارد. نکته قابل توجهی که درباره‌ی رولینگ وجود دارد، نام او در کنار سیاستمداران بزرگ جهان مثل ولادیمیر پوتین است. در سال 2007 مجله تایمز هم نام رولینگ را بعد از ولادیمیر پوتین به‌عنوان دومین شخص تأثیرگذار سال انتخاب کرد. مجله فوربز رولینگ را در رده چهل و هشتمین شخصیت معروف در سال ۲۰۰۷ معرفی کرد. رولینگ در حال حاضر فعالیت‌های انسان دوستانه و خیرخواهانه زیادی را به عهده گرفته و حمایت می‌کند.n>

کودکی و نوجوانی جی.کی رولینگ
جی کی رولینگ هم مثل بسیاری از نویسندگان دیگر از کودکی به نوشتن علاقه داست. گاهی هم می‌نوشت اما اوایل نوشته‌های خوبی برای ارائه نداشت. او از همین دوره به نام خانوادگی پاتر علاقه‌ی خاصی داشت. داستانی که از کودکی او بسیار مشهور شده است این است که زمانی که رولینگ 6 سال داشت یک داستان کوتاه درباره‌ی یک خرگوش نوشت و نام آن را خرگوشی به نام خرگوش گذاشت. مادر رولینگ بعد از خواندن این داستان از دخترش تعریف کرد و رولینگ در جواب به مادرش گفت «پس بیا چاپش کنیم». رولینگ سال‌ها بعد درباره‌ی این خاطره گفت است که نمی‌دانم این حرف از کجا به ذهنم رسید واقعاً عجیب بود.

بعد از فارغ‌التحصیلی از مدرسه رولینگ در امتحانات دانشگاه آکسفورد شرکت کرد اما قبول نشد. پدر و مادر رولینگ، او را تشویق کردند تا در دانشگاه اِکستر انگلستان، زبان فرانسه بخواند چرا که زبان فرانسه از نظر آنها پرکاربردتر از انگلیسی بود.

ایده‌ی هری پاتر
سال 1990 بود که اولین ایده‌ی داستان هری پاتر به ذهن رولینگ رسید. طبق گفته‌های رولینگ او زمانی که در یک سفر طولانی که با قطار داشت فکر نوشتن هری پاتر به ذهنش رسید و در همان جا شخصیت‌های این داستان را در ذهنش ساخت.

از افسردگی تا موفقیت
موفقیت جی. کی رولینگ به شکلی که ما امروزه می‌بینیم نبوده است او سختی‌های زیادی را در زندگی متحمل شده و از یک ایده‌ی ناب و کار کردن روی آن به این موقعیت رسیده است. رولینگ در سال 1990 بعد از فارغ‌التحصیلی از دانشگاه به‌عنوان منشی و مترجم مشغول به کار شد و بعد از دو سال با یک روزنامه‌نگار ازدواج کرد و حاصل این ازدواج دختری به نام جسیکا بود؛ اما بعد از دو سال رولینگ متوجه شد که همسرش مردی نیست که او در رویاهای خود می‌دیده است. درنهایت در سال 1993 بعد از یک دعوای سخت همسر رولینگ او را از خانه بیرون کرد و سرانجام آن‌ها از هم طلاق گرفتند. این طلاق در روحیه رولینگ تأثیر بدی گذاشت به‌قدری که او را تا مرز خودکشی کشاند. رولینگ که شغل خود را ازدست‌داده بود و با افسردگی دست‌وپنجه نرم می‌کرد سعی کرد با نویسندگی خودش را آرام کند و روی داستان هری پاتر که ایده‌ی آن سه سال قبل در ذهنش شکل‌گرفته بود کار کند.

انتشار اولین کتاب هری پاتر
رولینگ مدت‌ها به کافه‌ها می‌رفت و آنجا روی داستان خود کار می‌کرد تا سرانجام اولین کتابش را به پایان رساند و برای چندین انتشاراتی فرستاد. در ابتدا حدود دوازده موسسه بزرگ انتشاراتی کتابش را رد کردند؛ اما یک انتشاراتی کوچک قبول کرد با دستمزد 1500 یورو به‌عنوان دستمزد به رولینگ آن را منتشر کند. رولینگ نویسنده‌ای بود که با فقر و مخارج کم اما با امید زیاد به نوشتن ادامه داد. دخترش جسیکا یکی از خوانندگان او بود که داستانش را خوانده بود و مشتاق بود تا بداند انتهای داستان به کجا ختم می‌شود. رولینگ در خاطرات خود گفته است: «به این نتیجه رسیده بودم که تنها کاری که می‌خواهم انجام دهم نوشتن رمان است؛ اما پدر و مادرم که هر دو در خانواده‌های فقیری بزرگ‌شده و هیچ‌کدام به دانشگاه نرفته بودند، می‌گفتند که نیروی تخیلی من یک پدیده عجیب سرگرم‌کننده شخصی است که هرگز به‌جایی نمی‌رسد وزندگی مالی مرا تأمین نمی‌کند؛ اما حرفشان به گوش من نرفت. ازاین‌رو آن‌ها امیدوار بودند که مدرکی حرفه‌ای و کارآمد بگیرم؛ اما من می‌خواستم ادبیات انگلیسی بخوانم.»

رولینگ اولین کتابش را با نام خودش جوآن رولینگ امضا کرد اما چون جوانان در آن زمان به نویسندگان زن بها نمی‌دادند مدیر انتشارات بلومزبری از او خواست که کتابش را با نام مستعار امضا کند. به همین دلیل از آن به بعد روی کتاب‌های هری پاتر نام جی.کی.رولینگ ثبت شد و این اسم بر کتابهای او باقی ماند. در سال ۱۹۹۶ اولین سری کتاب‌های هری پاتر روانه‌ی بازار شد و در عرض چند هفته تمامی کتاب‌ها فروخته شد. اولین جلد کتاب هری پاتر ۱۲۰ میلیون نسخه بود که به سرعت به زبان‌های مختلف ترجمه و در سراسر دنیا به فروش رفت.

سال ۱۹۹۸ بود که داستان هری پاتر برای اولین بار تبدیل به فیلم شد و با درخواست رولینگ تمام بازیگران، از بازیگران بریتانیایی انتخاب شدند و در سال 2006 آخرین سری کتاب هری پاتر نوشته و منتشر شد. جی کی رولینگ به غیر از هری پاتر چند کتاب دیگر هم دارد.

جوایز و افتخارات جی کی رولینگ
از سال 1997 تا سال 2017 جی کی رولینگ جوایز و افتخارات متعددی کسب کرده است. او بیش از 4 بار موفق شده جایزه کتاب کودک سال بریتانیا را کسب کند. او در سال 2010 جایزه هانس کریستن آندرسن و در سال 2017 جایزه افتخاری خانواده سلطنتی بریتانیا را کسب کرد.

مجموعه هری پاتر
هری پاتر و سنگ جادو
هری پاتر و تالار اسرار
هری پاتر و زندانی آزکابان
هری پاتر و جام آتش
هری پاتر و محفل ققنوس
هری پاتر و شاهزاده دورگه
هری پاتر و یادگاران مرگ
هری پاتر و فرزند نفرین‌شد', '/assets/images/authors/author_1_1762279021.jpg', 'https://en.wikipedia.org/wiki/J._K._Rowling', '1', '2025-11-04 21:25:28');

DROP TABLE IF EXISTS `bookmarks`;
CREATE TABLE `bookmarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_persian_ci NOT NULL,
  `user_id` int NOT NULL,
  `chapter_id` int NOT NULL,
  `title` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `note` text COLLATE utf8mb4_persian_ci,
  `position_seconds` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `user_id` (`user_id`),
  KEY `chapter_id` (`chapter_id`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `bookmarks`
-- Table is empty

DROP TABLE IF EXISTS `books`;
CREATE TABLE `books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_persian_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `description` text COLLATE utf8mb4_persian_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `duration` int DEFAULT '0',
  `author_id` int DEFAULT NULL,
  `narrator_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `is_free` tinyint(1) DEFAULT '0',
  `is_featured` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `total_views` int DEFAULT '0',
  `average_rating` decimal(3,2) DEFAULT '0.00',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `narrator_id` (`narrator_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `books_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `authors` (`id`),
  CONSTRAINT `books_ibfk_2` FOREIGN KEY (`narrator_id`) REFERENCES `authors` (`id`),
  CONSTRAINT `books_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `books`
-- Table is empty

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `uuid` varchar(36) COLLATE utf8mb4_persian_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `description` text COLLATE utf8mb4_persian_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_persian_ci DEFAULT '#00BFA5',
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `slug` (`slug`),
  KEY `parent_id` (`parent_id`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `categories`
INSERT INTO `categories` (`id`, `parent_id`, `uuid`, `name`, `slug`, `description`, `cover_image`, `color`, `is_active`, `sort_order`, `created_at`) VALUES ('1', NULL, 'c6906be7-6534-4fdb-a63e-b7b1619af43a', 'کتاب صوتی', 'audiobooks', 'معرفی و دانلود بهترین کتاب‌های صوتی
کتاب های صوتی راهی عالی برای لذت بردن از مطالعه در هر زمان و مکان هستند؛ چه در مسیر رفت‌وآمد، چه در هنگام ورزش یا استراحت. با خرید و دانلود کتاب گویا شنوا ، دنیایی از داستان‌ها و دانش در قالبی حرفه‌ای و با صدای گویندگان توانمند در اختیار شماست. اگر به دنبال تجربه‌ای متفاوت از خواندن هستید، شنیدن این کتاب صوتی را از دست ندهید!
کتاب صوتی (کتاب گویا) چیست؟
کتاب صوتی یا کتاب گویا، کتابی است که متن آن توسط یک یا چند گوینده خوانده شده و به صورت فایل صوتی در اختیار مخاطبان قرار می‌گیرد. دانلود کتاب های صوتی مفهوم جدیدی نیست، اما در سال‌های اخیر محبوبیت زیادی پیدا کرده است.
یکی از مزایای کتابهای صوتی، راحتی و دسترسی آسان به آن است. کتاب گویا را می‌توان در هر زمان و مکانی شنید، بدون اینکه نیازی به حمل کتاب فیزیکی باشد. این امر به ویژه برای افرادی که فرصت زیادی برای مطالعه ندارند، بسیار مفید و کارآمد است.
مزیت دیگر دانلود کتاب صوتی، امکان لذت بردن از کتاب در حین انجام کارهای دیگر است. کتاب های صوتی را می‌توان در حین رانندگی، ورزش یا کار منزل گوش داد. این امر باعث می‌شود که بتوان در زمان‌هایی که به طور معمول نمی‌توان مطالعه کرد، از خواندن کتاب‌هایی که دوستشان داریم لذت برد.
انواع کتاب صوتی
کتاب صوتی‌ها در انواع مختلفی موجود هستند، از جمله رمان، ادبیات، کودک و نوجوان، علمی، روانشناسی، سبک زندگی و مدیریت و بازاریابی. این امر باعث می‌شود که بتوان کتاب‌هایی را پیدا کرد که با علاقه‌های مختلف افراد مطابقت داشته باشد.
کتاب های صوتی می‌تواند یک راه عالی برای یادگیری و سرگرمی باشد. اگر به دنبال یک راه جدید برای مطالعه هستید، کتاب گویا را امتحان کنید. در اینجا به چند نکته برای انتخاب و گوش دادن به کتاب صوتی اشاره می‌کنیم که ممکن است کیفیت خوانش صوتی آثار را بهبود بخشد:
نخست اینکه به موضوع و ژانر کتاب توجه کنید. مطمئن شوید که کتابی را انتخاب می‌کنید که با علاقه‌های شما مطابقت داشته باشد. سپس به گوینده کتاب توجه کنید. گوینده کتاب باید صدایی واضح و گیرا داشته باشد و بتواند شخصیت‌های داستان را به خوبی به تصویر بکشد.
معرفی پرفروش ترین کتاب های صوتی
 شنوا  یکی از بزرگترین پلتفرم‌های کتابخوانی در ایران است که علاوه بر کتاب‌های متنی، کتاب گویا نیز در آن عرضه می‌شود.
برخی از بهترین کتاب های صوتی شنوا عبارتند از:
•	سقوط اثر آلبر کامو، با صدای بهرام ابراهیمی
•	هزار خورشید تابان اثر خالد حسینی، با صدای رضا عمرانی
•	دختر که رهایش کردی اثر جوجو مویز، با صدای رضا عمرانی و راضیه هاشمی
•	نسخه صوتی مغازه خودکشی با صدای هوتن شکیبا
•	نسخه صوتی سمفونی مردگان با صدای حسین پاکدل
•	نسخه صوتی کتابخانه نیمه شب با صدای جمعی از گویندگان
•	نسخه صوتی یک عاشقانه آرام با صدای پیام دهکردی
این کتاب‌ها همگی از آثار پرفروش و محبوب هستند که توسط گویندگان حرفه‌ای اجرا شده‌اند.
اگر به دنبال یک راه جدید برای مطالعه هستید، خرید کتاب گویا شنوا را امتحان کنید. مطمئناً از آن‌ها لذت خواهید برد.
چرا کتابهای صوتی محبوب و پرطرفدار شده‌اند؟
در دنیای پرمشغله‌ی امروز، بسیاری از ما فرصت کافی برای نشستن و مطالعه‌ی کتاب نداریم. کتاب های صوتی با فراهم کردن امکان گوش دادن به محتوای ارزشمند در حین رانندگی، پیاده‌روی، ورزش یا کارهای روزمره، این مشکل را حل کرده‌اند.
از طرفی، روایت حرفه‌ای گویندگان باعث می‌شود تجربه‌ی شنیدن کتاب، لذت‌بخش‌تر و عمیق‌تر از خواندن سنتی باشد. تنوع بالای موضوعات، دسترسی آسان از طریق اپلیکیشن‌هایی مثل اپلیکیشن شنوا  و امکان یادگیری یا سرگرمی بدون نیاز به صفحه‌خوانی، از مهم‌ترین دلایل محبوبیت دانلود کتاب های صوتی هستند.
کتاب صوتی مناسب چه کسانی است؟
کتابهای صوتی انتخابی عالی برای افرادی است که در کنار دغدغه‌های روزمره، نمی‌خواهند از یادگیری یا لذت مطالعه عقب بمانند. اگر جزو یکی از گروه‌های زیر هستید، کتاب های صوتی دقیقاً برای شما ساخته شده‌اند:
•	افراد پُرمشغله که وقت آزاد کمی برای مطالعه دارند
•	دانشجویان و علاقه‌مندان به یادگیری که می‌خواهند از زمان‌های مرده‌شان بهترین استفاده را ببرند
•	علاقه‌مندان به داستان و رمان که شنیدن روایت یک داستان را جذاب‌تر از خواندن آن می‌دانند
•	افراد دارای اختلالات بینایی یا کسانی که خواندن برایشان سخت است
•	کسانی که در حین رانندگی، ورزش یا کارهای روزمره می‌خواهند مطالعه کنند
کتاب های صوتی، مطالعه را ساده، لذت‌بخش و در دسترس همه کرده‌اند — تنها با یک هدفون و چند دقیقه وقت آزاد.
کتاب صوتی همان پادکست است؟
خیر؛ کتاب صوتی و پادکست هرچند شباهت‌هایی دارند، اما دو قالب کاملاً متفاوت از محتوا هستند.
کتابهای صوتی نسخه‌ای صوتی از یک کتاب مکتوب است که معمولاً با صدای گوینده حرفه‌ای و با رعایت ساختار کامل کتاب (فصل‌بندی، لحن نویسنده و...) ضبط می‌شود. این محتوا دقیقاً بازتاب وفادارانه‌ای از متن اصلی کتاب است.
در مقابل، پادکست بیشتر حالت گفت‌وگویی یا محتوای آزاد دارد و معمولاً موضوع‌محور است، نه متن‌محور. پادکست‌ها ممکن است جنبه خبری، تحلیلی، سرگرمی یا آموزشی داشته باشند و لزوماً مبتنی بر کتاب یا متن مشخصی نیستند.
کتاب های صوتی را با صدای چه کسانی بشنویم؟
یکی از جذاب‌ترین بخش‌های تجربه شنیدن و دانلود کتاب صوتی، صدای گوینده‌ای است که روح تازه‌ای به کلمات می‌بخشد. در شنوا ، کتاب های صوتی با صدای هنرمندان و گویندگان حرفه‌ای تولید می‌شوند که شنیدن صدای آن‌ها خود به‌تنهایی یک لذت است.
از جمله صدای گرم و متفاوت هوتن شکیبا، روایت دلنشین و هنرمندانه‌ی شرمین نادری، اجرای پرانرژی و تاثیرگذار آرمان سلطان‌زاده، صدای عمیق و ماندگار رضا عمرانی، صدای نوستالژیک بهرام شاه محمدلو، تا صدای خاص و منحصربه‌فرد محمود دولت‌آبادی که خود آثارش را روایت کرده است — همه و همه تجربه‌ای متفاوت از کتاب‌خوانی برایتان رقم خواهند زد.
با این صداها، کتاب شنیدن نه‌فقط آسان، بلکه به‌شدت لذت‌بخش می‌شود.
دانلود کتابهای صوتی در شنوا
شنوا یکی از بزرگ‌ترین و معتبرترین پلتفرم‌های کتاب الکترونیکی و صوتی در ایران است که با آرشیوی غنی از هزاران عنوان کتاب صوتی، تجربه‌ای جذاب و راحت از شنیدن کتاب را فراهم کرده است.
برای دانلود کتاب های صوتی در شنوا ، تنها کافی‌ست اپلیکیشن را روی گوشی یا تبلت خود نصب کنید، کتاب مورد نظرتان را جستجو کرده و با یک کلیک آن را به کتابخانه‌تان اضافه کنید.
امکان دانلود برای شنیدن آفلاین، تنظیم سرعت پخش، و تایمر خواب از جمله امکانات ویژه‌ای هستند که شنوا برای لذت‌بخش‌تر کردن تجربه شنیدن کتاب در اختیار شما قرار داده است.
چه به دنبال دانلود کتاب های صوتی رایگان باشید و چه آثار پرفروش نویسندگان مشهور، در شنوا همیشه چیزی برای گوش دادن وجود دارد.', '/assets/images/categories/audiobooks_1762279846.webp', '#00BFA5', '1', '0', '2025-11-04 21:40:46');
INSERT INTO `categories` (`id`, `parent_id`, `uuid`, `name`, `slug`, `description`, `cover_image`, `color`, `is_active`, `sort_order`, `created_at`) VALUES ('2', '1', '0ca0a7d9-6aea-42b8-9eff-92c5c3268b21', 'داستان و رمان', 'story', 'معرفی و دانلود بهترین کتاب‌های صوتی داستان و رمان
چقدر به شنیدن کتاب‌های صوتی داستان و رمان علاقه‌مند هستید؟ تا‌به‌حال داستان و رمان را به صورت صوتی گوش داده‌اید؟ شما کدام یک را ترجیح می‌دهید؟ کتاب متنی یا صوتی؟ این روزها که سرانه مطالعات کشور به شدت پایین است و دیگر کسی سراغ کتاب نمی‌رود، سوق دادن نوجوانان به داستان‌ها و رمان‌های صوتی می‌تواند راهگشا باشد. بسیاری از نوجوانان تمایل به شنیدن فایل صوتی دارند تا خواندن متن. این البته منحصر به نوجوانان نیست و کسر قابل توجهی از گروه‌های سنی دیگر نیز این‌طور فکر می‌کنند. مهم‌ترین مزیت خرید کتاب‌های داستان و رمان صوتی این است که می‌توان در طول روز در کنار دیگر کارها به آن گوش فرا داد. 
 مزیت‌های کتاب صوتی آن‌قدری است که اگر یک بار آن را امتحان کنید، احتمالا طرفدار آن خواهید شد. پس همین حالا شروع کنید و کتاب صوتی مورد علاقه خود را از شنوا دانلود کنید. ما در این مطلب تعدادی از بهترین کتاب‌های داستان و رمان صوتی را که می‌توانید از شنوا تهیه کنید، به شما معرفی خواهیم کرد.

معرفی کتاب‌های صوتی پرفروش داستان و رمان 
آثار فوق‌العاده جذاب و دلنشین هوشنگ مرادی کرمانی همچون داستان‌های ماندگار قصه‌های مجید و مربای شیرین از جمله پرفروش‌ترین کتاب‌های داستان حوزه نوجوان است. مرادی کرمانی با خلق آثار دلنشینش یکی از برترین نویسندگان تاریخ معاصر است. شما می‎توانید این کتاب‌های صوتی را از شنوا دانلود کنید.
داستان‌های علمی تخیلی و در راس آن ماجراهای هری پاتر در نسل نوجوان فعلی مخاطب بسیاری دارد. در این بین کتاب‌های صوتی هری پاتر و حفره اسرار آمیز و هری پاتر و محفل ققنوس از کتاب‌هایی هستند که مخاطبان شنوا بسیار از آن استقبال کرده‌اند. شما می‌توانید با مراجعه به اپلیکیشن و یا سایت شنوا این کتاب صوتی را بشنوید.
دیگر کتاب صوتی پرفروش داستان و رمان که به شما توصیه می‌کنیم شاهکار آنتوان دوسنت اگزوپری یعنی شازده کوچولو است. این کتاب که نخستین بار سال 1943 منتشر شده از پرتیراژترین کتاب‌های تاریخ است. فایل صوتی رمان شازده کوچولو در شنوا در دسترس است.

دانلود کتاب‌های پرطرفدار داستان و رمان صوتی
یکی از کتاب‌های پرطرفدار حوزه نوجوان داستان ماشالله خان و بارگاه هارون‌الرشید است. کتابی که اگرچه برای رده سنی نوجوان نوشته شده اما به اعتقاد نویسنده برجسته آن، ایرج پزشکزاد احتمالا افراد سالخورده هم از آن لذت ببرند. شما می‌توانید این کتاب پرطرفدار را از سایت شنوا به‌صورت صوتی تهیه کنید.
کتاب زنان کوچک اثر به‌یادماندنی لوئیزا می اوکالت در اواسط قرن 19 میلادی نوشته شده است. این رمان الهام‌بخش بعد از این همه سال همچنان یکی از کتاب‌های پرطرفدار در دنیا است. برای تهیه این کتاب صوتی شما می‌توانید از سایت شنوا بازدید کنید.  در بین همه آثار مارک تواین، نویسنده شهیر آمریکایی، داستان شاهزاده و گدا از محبوب‌ترین کتاب‌های اوست. کتابی که بارها در سراسر جهان تجدید چاپ شده و به زبان‌های مختلف ترجمه شده است. این کتاب روایتی از دوستی پسری فقیر با یک شاهزاده است. در این اثر هر دو نفر به جایگاه یکدیگر غبطه می‌خورند و همین امر باعث داستان‌های جالبی برای این دو می‌شود. گوش‌دادن به این کتاب صوتی در اپلیکیشن رایگان شنوا امکان‌پذیر است.', '/assets/images/categories/story_1762281238.jpg', '#00bfa5', '1', '0', '2025-11-04 22:03:58');
INSERT INTO `categories` (`id`, `parent_id`, `uuid`, `name`, `slug`, `description`, `cover_image`, `color`, `is_active`, `sort_order`, `created_at`) VALUES ('3', '1', '05ca78b9-ff47-4be8-a393-bc79aa1497ea', 'داستان و رمان خارجی', 'story-foreign', 'معرفی و دانلود بهترین کتاب‌های صوتی داستان و رمان خارجی
خرید و دانلود بهترین کتاب‌های صوتی داستان و رمان خارجی
بیایید باهم یک سفر پرماجرا به دنیای کتاب‌های صوتی داستان و رمان خارجی کنیم. در این دنیای جادویی شما با شخصیت‌های جذاب و ماجراهای متنوع آشنا خواهید شد. هر گامی که برداشته و هر صفحه‌ای که برگردانده می‌شود، به ماجراهای جدیدی خواهید رسید و در دنیایی کاملاً جدید فرو خواهید رفت. این سفر ماجراجویانه پر از هیجان و شگفتی‌های بی‌پایان است که شمارا به دنیایی خارق‌العاده می‌برد. از دیدن شگفتی‌ها و تجربه‌ی لحظات هیجان‌انگیز لذت می‌برید.
داستان‌ها و رمان‌های خارجی شامل داستان‌هایی از جهات و فرهنگ‌های مختلف هستند که می‌توانند خواننده را به دنیایی جدید و متفاوت ببرند. این آثار معمولاً توسط نویسندگان مطرحی نوشته می‌شود که با استفاده از سبک‌ها و روایت‌های متنوع خواننده را به دنیای خود می‌کشانند و احساسات مختلفی را در آن‌ها به تصویر می‌کشند. بهترین کتاب‌های صوتی داستان و رمان خارجی شامل داستان‌های متنوعی در ژانرهای مختلفی مانند رمان عاشقانه، علمی-تخیلی، جنایی، فانتزی و غیره است. شما می‌توانید از طریق شنوا بهترین کتاب‌های صوتی داستان و رمان خارجی را دانلود کنید و از شنیدن آن‌ها نهایت لذت را ببرید.
معرفی کتاب‌های پرفروش صوتی داستان و رمان خارجی
استفاده از بهترین کتاب‌های صوتی داستان و رمان خارجی به‌جای رمان‌های کاغذی امکانات بیشتری را به شما ارائه می‌دهد. با گوش دادن به رمان‌های صوتی، شما نیازی به حمل کتاب کاغذی ندارید و می‌توانید همیشه کتاب صوتی خود را در دسترس داشته باشید. درجاهایی که امکان مطالعه کتاب ممکن نیست مانند رانندگی، ورزش کردن و غیره می‌توانید از طریق گوش دادن ادامه رمان خود را پیگیری کنید.
 گوش دادن به رمان‌های صوتی به بهبود مهارت‌های شنیداری شما کمک می‌کند. شما با گوش دادن به داستان‌ها و متن‌های مختلف با لغات و اصطلاحات جدید آشنا خواهید شد. از طرف دیگر، برخی رمان‌های صوتی با استفاده از اثرات صوتی، صداها و نقش‌آفرینی‌های متنوع تجربه‌ای جذاب و واقعی‌تر از داستان را به خواننده ارائه می‌دهند. رمان‌های صوتی به‌عنوان یک گزینه مدرن و جذاب تجربهٔ مطالعه را برای خواننده بهتر می‌کنند و امکانات جدیدی را در اختیار آن‌ها قرار می‌دهند که موجب صرفه‌جویی در وقت و هزینه آن‌ها می‌شود. از حمله نمونه‌های پرفروش کتاب‌های صوتی داستان و رمان خارجی می‌توان به موارد زیر اشاره کرد.

· کتاب صوتی سقوط اثر آلبرکامو
·کتاب صوتی شرط بندی اثر آنتون چخوف
· کتاب صوتی کیمیاگر اثر پائولو کوئیلو
· کتاب صوتی پیرمرد و دریا اثر ارنست همینگوی
· کتاب صوتی ملت عشق اثرالیف شافاک
·کتاب صوتی وقتی نیچه گریست اثر اروین یالوم

دانلود کتاب‌های رایگان صوتی داستان و رمان خارجی
خواندن رمان‌ها یک فعالیت سرگرم‌کننده و آموزنده است. این فعالیت نه‌تنها برای سرگرمی و لذت بردن انجام می‌شود، بلکه به شما امکان می‌دهد تا در زمینه‌های مختلفی ازجمله زبان، تاریخ، فرهنگ، روانشناسی و دیگر موضوعات علمی و فرهنگی دانش و آگاهی خود را گسترش دهید. گوش دادن به رمان باعث تقویت مهارت‌های ذهنی شما نیز می‌شود؛ زیرا خواندن رمان‌ها نیازمند تمرکز، تفکر، حافظه و تخیل است.
هنگامی‌که شما با یک داستان جذاب و شخصیت‌های دل‌نشین آن آشنا می‌شوید، می‌توانید با قوه تخیل خود به دنیای‌ جدیدی که در داستان مطرح می‌شود، فرو بروید. این تجربه، علاوه بر اینکه برای لذت بردن و سرگرمی است به افزایش قدرت تخیل و خلاقیت شما نیز کمک می‌کند. وقتی‌که شما با داستان و شخصیت‌های جذاب یک رمان سرگرم هستید، به استرس‌های روزمره خود کمتر فرصت فکر کردن دارید که نقش مهمی در تقویت روحیه شما دارد. خواندن کتاب‌های صوتی داستان و رمان خارجی نه‌تنها یک سرگرمی است بلکه یک تجربه آموزنده و مفید منتقل می‌کند. شنوا به‌منظور کسب رضایت مخاطبان خود اقدام به ارائه‌ی برخی از کتاب‌های صوتی داستان و رمان خارجی به‌صورت رایگان کرده است. برای دانلود کتاب صوتی داستان خارجی می‌توانید از طریق شنوا اقدام کنید.

·کتاب صوتی رایگان پاسیو مشترک نویسنده میراندا جولای
·کتاب صوتی رایگان سکوت نویسنده ادگار آلن‌پو
·کتاب صوتی سوظن نویسنده ادگار والاس
·کتاب صوتی رایگان استعفای باشکوه نویسنده میچی ان جی
·کتاب صوتی رایگان پلیدی نویسنده کریستین بوبن
· کتاب صوتی رایگان پدر جودی آشغال است نویسنده ماتسو لیچت', '/assets/images/categories/story-foreign_1762281295.jpg', '#00BFA5', '1', '0', '2025-11-04 22:04:55');
INSERT INTO `categories` (`id`, `parent_id`, `uuid`, `name`, `slug`, `description`, `cover_image`, `color`, `is_active`, `sort_order`, `created_at`) VALUES ('4', '1', 'b6619193-4b02-4797-9445-d7fea1467d5e', 'فانتزی', 'story-foreign-fantasy', 'معرفی و دانلود بهترین کتاب‌های صوتی داستان و رمان فانتزی خارجی
خرید و دانلود کتاب‌های صوتی داستان و رمان فانتزی

ادبیات دریایی از ژانرهای متنوع است که هرکدام خوانندگان خاص خود را دارند. در میان این ژانرها، فانتزی با جذابیت بی‌نظیرش، طیف وسیعی از مخاطبان را در هر سنی به خود جذب می‌کند.

در دنیای فانتزی، نویسنده با تلفیق اساطیر، افسانه‌ها و عناصر دنیای واقعی، دریچه‌ای به سوی تجربه‌ای متفاوت می‌گشاید. داستان‌هایی که غالباً فراتر از درک عادی بشر هستند و با وجود پرداختن به مسائل دنیای واقعی، مسیر حل مسئله و روند داستان را به گونه‌ای غیرمعمول و جذاب روایت می‌کنند. اگر به دنبال لذتی منحصر به فرد در مطالعه هستید، قفسه کتاب‌های فانتزی منتظر شماست تا با دنیایی سرشار از شگفتی و تخیل روبرو شوید. کتابهای فانتزی صوتی در دوران‌ معاصر بسیار مورد توجه مخاطبان فارسی قرار گرفته و ناشران صوتی زیادی شروع به تولید کتاب‌های صوتی فانتزی کرده‌اند.



تاریخچه‌ای کوتاه از کتابهای فانتزی


ریشه‌های فانتزی به داستان‌های هزار و یک شب (حدود 700 تا 1500 سال پیش) بازمی‌گردد و کم کم به پیش آمده تا به مجموعه‌های شگفتی چون ارباب حلقه‌ها رسیده است. در جهان کتاب‌های فانتزی نقطه عطف‌های زیادی داشته که قطعا یکی از مهم‌ترین آنها ارباب حلقه‌هاست.

انتشار این کتاب اثر جورج آر.آر. تالکین در قرن بیستم، نگاه دنیا به فانتزی را دگرگون کرد و مسیر را برای خلق آثار ماندگار دیگر هموار نمود.

از آن زمان، موجودات عجیب و غریب، افسانه‌ها، شمشیرهای جادویی، وردهای خاص و دنیای شگفت‌انگیز آمیخته با واقعیت، به عنصری محبوب در ژانر فانتزی تبدیل شدند.

امروزه شاهد آثار فانتزی بی‌نظیری در سراسر جهان هستیم و بسیاری از فیلم‌های پرفروش نیز بر اساس این ژانر ساخته می‌شوند.



چرا گوش دادن به کتابهای فانتزی جذاب است؟

گوش دادن به داستان‌های فانتزی حال و هوای متفاوتی دارد چون از مرزهای تخیل و تصور بسیار فراتر رفته و در دنیای خیالات بی‌انتها قدم می‌گذاریم. در حال حاضر ناشران صوتی با استفاده از صداپیشگان با سابقه رادیو این کتاب‌ها را به صورت گروهی و در پروژه‌های بزرگ تولید می‌کنند.



پرفروش‌ترین کتاب‌های صوتی فانتزی
در میان کتاب‌های صوتی فانتزی تاکنون کتاب‌های زیادی منتشر شده که بسیاری از آنها با استقبال زیاد مخاطبان روبرو شده است. از میان به پنج کتاب صوتی پرفروش فانتزی را به شما معرفی می‌کنیم:

کتاب صوتی ارباب حلقه‌ها
کتاب صوتی هری پاتر و محفل ققنوس
کتاب صوتی کوری
کتاب صوتی تلماسه
کتاب صوتی 1984', '/assets/images/categories/story-foreign-fantasy_1762281368.jpg', '#00BFA5', '1', '0', '2025-11-04 22:06:08');

DROP TABLE IF EXISTS `chapters`;
CREATE TABLE `chapters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_persian_ci NOT NULL,
  `book_id` int NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `chapter_number` int NOT NULL,
  `audio_url` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `duration` int NOT NULL,
  `file_size` bigint DEFAULT '0',
  `plays_count` int DEFAULT '0',
  `is_free` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `idx_book_order` (`book_id`,`sort_order`),
  CONSTRAINT `chapters_ibfk_1` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `chapters`
-- Table is empty

DROP TABLE IF EXISTS `listening_history`;
CREATE TABLE `listening_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `chapter_id` int NOT NULL,
  `book_id` int NOT NULL,
  `progress_seconds` int DEFAULT '0',
  `percentage` decimal(5,2) DEFAULT '0.00',
  `completed` tinyint(1) DEFAULT '0',
  `last_listened_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `chapter_id` (`chapter_id`),
  KEY `book_id` (`book_id`),
  KEY `idx_user_listening` (`user_id`,`last_listened_at`),
  CONSTRAINT `listening_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `listening_history_ibfk_2` FOREIGN KEY (`chapter_id`) REFERENCES `chapters` (`id`),
  CONSTRAINT `listening_history_ibfk_3` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `listening_history`
-- Table is empty

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_persian_ci NOT NULL,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `rating` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `comment` text COLLATE utf8mb4_persian_ci,
  `is_approved` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `unique_user_book_review` (`user_id`,`book_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_chk_1` CHECK (((`rating` >= 1) and (`rating` <= 5)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `reviews`
-- Table is empty

DROP TABLE IF EXISTS `user_favorites`;
CREATE TABLE `user_favorites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_book` (`user_id`,`book_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `user_favorites`
-- Table is empty

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) COLLATE utf8mb4_persian_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_persian_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_persian_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_persian_ci NOT NULL,
  `display_name` varchar(100) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_persian_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `is_premium` tinyint(1) DEFAULT '0',
  `dark_mode` tinyint(1) DEFAULT '0',
  `sleep_timer_enabled` tinyint(1) DEFAULT '0',
  `sleep_timer_duration` int DEFAULT '0',
  `driving_mode` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_persian_ci;

-- Data for table `users`
INSERT INTO `users` (`id`, `uuid`, `username`, `email`, `password_hash`, `display_name`, `avatar_url`, `is_active`, `is_premium`, `dark_mode`, `sleep_timer_enabled`, `sleep_timer_duration`, `driving_mode`, `created_at`, `updated_at`) VALUES ('1', 'a4d5b071-8f51-4b31-b6e7-a6427fec3c94', 'admin', 'namayandeshayan@gmail.com', '$2y$10$wMV2XQQDwMA1ZPVtcWnx5.Xug17tbi0Rega47h5wPDcvaFR9.mwYG', 'شایان نماینده', NULL, '1', '1', '1', '0', '0', '0', '2025-11-04 20:23:24', '2025-11-04 20:28:11');

SET FOREIGN_KEY_CHECKS=1;
