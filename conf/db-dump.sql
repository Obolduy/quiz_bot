DROP TABLE IF EXISTS `answers`;
CREATE TABLE `answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `answer` text NOT NULL,
  PRIMARY KEY (`id`)
);

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
INSERT INTO `answers` VALUES (9,3,'ОТВЕТ1'),(10,3,'НОВЫЙ ОТВЕТ 2'),(11,3,'ОТВЕТ3'),(12,3,'ОТВЕТОТВЕТ'),(13,4,'ОТВЕТОТВЕТОТВЕТОТВЕТОТВЕТ'),(14,4,'ОТВЕТВМААВ'),(15,4,'ОТВЕТВТКЁ'),(16,4,'Новый ответ'),(17,5,'ОТВЕТ1111111'),(18,5,'ОТВЕТ2222'),(19,5,'ОТВЕТ3333'),(20,5,'ОТВЕТ1'),(21,6,'вао'),(22,6,'счм'),(23,6,'ип'),(24,6,'ку'),(25,7,'аыавываываыв'),(26,7,'смчмчсм'),(27,7,'ерере'),(28,7,'45ак'),(29,8,'разумеется'),(30,8,'конечно'),(31,8,'очевидно'),(32,8,'а это непраивльный ответ'),(33,9,'слушай ну нормально'),(34,9,'такое себе на самом деле'),(35,9,'очень хорошо'),(36,9,'не важно'),(37,10,'слушай а он не срет же'),(38,10,'вроде вчера'),(39,10,'щас срет'),(40,10,'в 11 вечера'),(41,11,'Умрёшь'),(42,11,'Вано не умеет топттб печек'),(43,11,'Я хачу твикс😩🤌🏻'),(44,11,'Тебя вылечат'),(45,12,'Че громко так э😡'),(46,12,'Я так не могу😔✋🏻'),(47,12,'Нихуёво'),(48,12,'Бля владос заебал'),(49,13,'Гарик стонет на фоне'),(50,13,'А Влад подсматривает'),(51,13,'Груша'),(52,13,'Яблоко 🍎'),(53,14,'1'),(54,14,'5'),(55,14,'35'),(56,14,'-7'),(57,15,'108'),(58,15,'66'),(59,15,'2метра'),(60,15,'Готово'),(61,16,'да какие угодно лишь бы влад прикрылся уже господи'),(62,16,'запеченые но с глистами'),(63,16,'запеченые'),(64,16,'со вкусом глистов'),(65,17,'извините а какие наркотики? только пиво'),(66,17,'виноградный'),(67,17,'а с земли лишь бы'),(68,17,'нет вика такое не делает'),(69,18,'ну точно не его'),(70,18,'пиво'),(71,18,'суши'),(72,18,'палаелероялеоченьсильно'),(73,19,'сидеть в доме без родителей с владиком'),(74,19,'мыться в душе в холодной воде'),(75,19,'ну тут сложно сказать может есть роллы но нет наверное ругаться матом лол а ты же и не увидишь всего текста наверное'),(76,19,'снимать трусы очевидно же она так больше не может'),(77,20,'ответетет'),(78,20,'ваывыа'),(79,20,'нрк'),(80,20,'ек'),(81,21,'ахахах'),(82,21,'АХАХАХ'),(83,21,'ААВ'),(84,21,'М'),(85,22,'вм'),(86,22,'куц'),(87,22,'мсч'),(88,22,'ыва'),(89,40,'твоаывоа'),(90,40,'кам'),(91,40,'ка'),(92,40,'вм'),(93,41,'ваы'),(94,41,'уцк'),(95,41,'мс'),(96,41,'ы'),(97,42,'вмв'),(98,42,'ва'),(99,42,'см'),(100,42,'у'),(101,43,'куе'),(102,43,'вап'),(103,43,'чмс'),(104,43,'ук'),(105,62,'акукуккуукукукпукп'),(106,62,'ва'),(107,62,'ук'),(108,62,'ав'),(109,63,'мьиьииьс'),(110,63,'уклукук'),(111,63,'чсмчсмсмч'),(112,63,'уцкцуцу'),(113,64,'кибке'),(114,64,'ку'),(115,64,'м'),(116,64,'в'),(129,68,'рнепвуцы1'),(130,68,'ап2'),(131,68,'апм3'),(132,68,'ав4'),(133,69,'кпткгтги'),(134,69,'куукукееук'),(135,69,'мсмсс'),(136,69,'кк'),(137,70,'111111111'),(138,70,'222222'),(139,70,'3333'),(140,70,'44444');
/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `correct_answers`
--

DROP TABLE IF EXISTS `correct_answers`;
CREATE TABLE `correct_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question_id` int(11) NOT NULL,
  `answer_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `correct_answers`
--

LOCK TABLES `correct_answers` WRITE;
/*!40000 ALTER TABLE `correct_answers` DISABLE KEYS */;
INSERT INTO `correct_answers` VALUES (3,3,10),(4,4,14),(5,5,19),(6,6,22),(7,7,25),(8,8,29),(9,9,33),(10,10,38),(11,11,43),(12,12,46),(13,13,51),(14,16,61),(15,17,68),(16,18,71),(17,19,73),(18,20,78),(19,21,82),(20,40,90),(21,41,95),(22,43,103),(23,62,105),(24,63,110),(25,64,115),(29,68,129),(30,69,134),(31,70,139);
/*!40000 ALTER TABLE `correct_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `current_user_quiz`
--

DROP TABLE IF EXISTS `current_user_quiz`;
CREATE TABLE `current_user_quiz` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `passed_question_id` int(11) NOT NULL,
  `passed_answer_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `current_user_quiz`
--

LOCK TABLES `current_user_quiz` WRITE;
/*!40000 ALTER TABLE `current_user_quiz` DISABLE KEYS */;
/*!40000 ALTER TABLE `current_user_quiz` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `connection` text COLLATE utf8_unicode_ci NOT NULL,
  `queue` text COLLATE utf8_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
);

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2019_08_19_000000_create_failed_jobs_table',1),(2,'2019_12_14_000001_create_personal_access_tokens_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passed_quizes`
--

DROP TABLE IF EXISTS `passed_quizes`;
CREATE TABLE `passed_quizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `passed_quiz_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_score` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `passed_quizes`
--

LOCK TABLES `passed_quizes` WRITE;
/*!40000 ALTER TABLE `passed_quizes` DISABLE KEYS */;
INSERT INTO `passed_quizes` VALUES (1,1,810293946,1),(2,1,810293946,0),(3,2,810293946,1),(4,4,435695550,0),(5,1,229404916,0),(6,4,229404916,0),(7,4,229404916,2),(8,4,229404916,1),(9,5,810293946,1),(10,5,810293946,2),(11,7,229404916,1),(12,8,810293946,1),(13,8,810293946,1),(14,8,810293946,1),(15,8,810293946,1),(16,8,810293946,1),(17,8,810293946,1),(18,8,810293946,1),(19,8,810293946,1),(20,8,810293946,1),(21,8,810293946,0),(22,8,810293946,0),(23,8,810293946,1),(24,8,810293946,0),(25,7,810293946,0),(26,8,810293946,1),(27,8,810293946,0),(28,8,810293946,1),(29,5,810293946,2),(30,7,810293946,2),(31,1,810293946,1),(32,2,810293946,2),(33,2,810293946,0),(34,2,810293946,2),(35,7,810293946,1),(36,7,810293946,1),(37,40,810293946,3),(38,40,810293946,0),(39,5,810293946,2),(40,41,810293946,2);
/*!40000 ALTER TABLE `passed_quizes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
);

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_pictures`
--

DROP TABLE IF EXISTS `question_pictures`;
CREATE TABLE `question_pictures` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `question_id` int(11) DEFAULT NULL,
  `picture` text NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `question_pictures`
--

LOCK TABLES `question_pictures` WRITE;
/*!40000 ALTER TABLE `question_pictures` DISABLE KEYS */;
INSERT INTO `question_pictures` VALUES (1,22,'c3658e82ae81d23b191134f48d5fec35.jpg'),(13,68,'0abf20b7769f38b8d8655cb6ee05afb8.jpg'),(14,70,'b8ada9014537eee42a94aeeaa6978a27.jpg'),(15,69,'fb029b061d6e3d9b13168866005ba73e.jpg');
/*!40000 ALTER TABLE `question_pictures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
CREATE TABLE `questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `quiz_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (3,'Вопрос один новое?',2),(4,'Вопросик 2?',2),(5,'вопрос 3 кстати?',2),(6,'Перви?',3),(7,'втаро?',3),(8,'а владик может ли отдаться за 5 рублей?',4),(9,'а как владик относится к тебе?',4),(10,'во скока я срал?',4),(11,'Что будет если простудится в Омжрике😘?',5),(12,'Фраза после сглатывания?',5),(13,'Груша или яблоко?',5),(14,'Какой размер пениса у меня?',6),(15,'Какой размер пениса в меня поместиться?',6),(16,'ЛЮБИМЫЕ РОЛЛЫ?',7),(17,'ЛЮБИМЫЙ ВКУС СНЮСА?',7),(18,'КОГО ОНА ЛЮБИТ?',7),(19,'ЛЮБИМОЕ ЗАНЯТИЕ?',7),(20,'Вопрос 1?',8),(21,'Вопрос 2!!?',8),(22,'лвмваммавмва?',9),(40,'вопрос один?',27),(41,'второй?',27),(42,'ава?',28),(43,'кеку?',29),(62,'ВАПРОС 1?',39),(63,'вапрос 2?',39),(64,'ВАПРОС 3?',39),(68,'ВОПРОС С БЛЯДСКОЙ КАРТИНКОЙ АХАХАХАХАХАХАХХАХАХАХАХАХАХ ТИГОР?',41),(69,'вопрос с картинкой?',41),(70,'тут картинка тож?',41);
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz_stars`
--

DROP TABLE IF EXISTS `quiz_stars`;
CREATE TABLE `quiz_stars` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `quiz_id` int(11) NOT NULL,
  `votes_count` int(11) DEFAULT NULL,
  `stars_avg` int(11) DEFAULT NULL,
  `stars_count` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `quiz_stars`
--

LOCK TABLES `quiz_stars` WRITE;
/*!40000 ALTER TABLE `quiz_stars` DISABLE KEYS */;
INSERT INTO `quiz_stars` VALUES (1,8,6,3,17),(2,1,1,2,2),(3,2,8,2,16),(4,3,NULL,NULL,NULL),(5,4,NULL,NULL,NULL),(6,5,3,4,13),(7,6,NULL,NULL,NULL),(8,7,4,3,13),(9,9,NULL,NULL,NULL),(27,27,NULL,NULL,NULL),(28,28,NULL,NULL,NULL),(29,29,NULL,NULL,NULL),(39,39,NULL,NULL,NULL),(40,40,2,3,6),(41,41,1,1,1);
/*!40000 ALTER TABLE `quiz_stars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quizes`
--

DROP TABLE IF EXISTS `quizes`;
CREATE TABLE `quizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `creator_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
);

--
-- Dumping data for table `quizes`
--

LOCK TABLES `quizes` WRITE;
/*!40000 ALTER TABLE `quizes` DISABLE KEYS */;
INSERT INTO `quizes` VALUES (2,'2 квизик',810293946),(3,'Ну давай выберем название',810293946),(4,'специальный тест для дибила',810293946),(5,'Пошёл нахуй со своей корректностью',229404916),(6,'Выбор пениса',435695550),(7,'НЕКОРРЕКТНАЯ МАТЕРШИННИЦА ДУРА',810293946),(8,'Тестовая викторина для оценочек',810293946),(9,'вопрос с фотой',810293946),(27,'акаау',810293946),(28,'fvdsfdvfdvdfv',810293946),(29,'тртрр',810293946),(39,'ВОпрос',810293946),(41,'Векторина с фотками',810293946);
/*!40000 ALTER TABLE `quizes` ENABLE KEYS */;
UNLOCK TABLES;