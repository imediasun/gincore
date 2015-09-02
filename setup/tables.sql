-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Апр 02 2013 г., 11:43
-- Версия сервера: 5.5.29-log
-- Версия PHP: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `u_site`
--

-- --------------------------------------------------------

--
-- Структура таблицы `def_image_titles`
--

CREATE TABLE IF NOT EXISTS `def_image_titles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gallery` varchar(255) CHARACTER SET utf8 NOT NULL,
  `image` varchar(255) CHARACTER SET utf8 NOT NULL,
  `name` text CHARACTER SET utf8 NOT NULL,
  `name_en` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=cp1251 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `def_map`
--

CREATE TABLE IF NOT EXISTS `def_map` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `domain` smallint(5) unsigned NOT NULL,
  `section` smallint(5) unsigned NOT NULL,
  `lang` smallint(5) unsigned NOT NULL,
  `prio` mediumint(9) NOT NULL,
  `parent` int(10) unsigned NOT NULL,
  `redirect` int(10) unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `state` tinyint(1) unsigned NOT NULL,
  `template_header` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `template` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `template_inner` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `template_footer` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'default',
  `fullname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `metadescription` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
  `metakeywords` varchar(400) COLLATE utf8_unicode_ci NOT NULL,
  `meta` varchar(512) COLLATE utf8_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `gallery` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `picture` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `picture2` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `is_gmap` tinyint(1) NOT NULL DEFAULT '0',
  `lat` float(10,6) NOT NULL DEFAULT '0.000000',
  `lng` float(10,6) NOT NULL DEFAULT '0.000000',
  `is_page` tinyint(1) NOT NULL DEFAULT '1',
  `page_type` smallint(6) NOT NULL,
  `uxt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `page_color` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Дамп данных таблицы `def_map`
--

INSERT INTO `def_map` (`id`, `url`, `domain`, `section`, `lang`, `prio`, `parent`, `redirect`, `name`, `state`, `template_header`, `template`, `template_inner`, `template_footer`, `fullname`, `metadescription`, `metakeywords`, `meta`, `content`, `gallery`, `picture`, `picture2`, `is_gmap`, `lat`, `lng`, `is_page`, `page_type`, `uxt`, `page_color`) VALUES
(1, '', 0, 1, 0, 0, 0, 0, 'Главная страница', 1, 'h_default', 'h_default', 'default', '', '', '', '', '', 'авыпвапываып вап ывап ывап ', '', '', '', 0, 0.000000, 0.000000, 1, 0, '2013-03-28 14:41:17', ''),
(2, 'uslugi', 0, 1, 0, 1, 0, 0, 'Услуги', 1, 'v_default', 'v_default', 'default', '', '', '', '', '', '', '', '', '', 0, 0.000000, 0.000000, 1, 0, '2013-03-28 14:41:46', ''),
(3, 'galereya', 0, 1, 0, 2, 0, 0, 'Галерея', 1, 'v_default', 'v_default', 'default', '', '', '', '', '', '', '', '', '', 0, 0.000000, 0.000000, 1, 0, '2013-03-28 14:44:17', ''),
(4, 'contacts', 0, 1, 0, 3, 0, 0, 'Контактная информация', 1, 'v_default', 'v_default', 'default', '', '', '', '', '', '', '', '', '', 0, 0.000000, 0.000000, 1, 0, '2013-03-28 14:45:32', '');

-- --------------------------------------------------------

--
-- Структура таблицы `def_map_module`
--

CREATE TABLE IF NOT EXISTS `def_map_module` (
  `page_id` int(10) unsigned NOT NULL,
  `module` varchar(255) NOT NULL,
  KEY `page_id` (`page_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `def_map_module`
--

INSERT INTO `def_map_module` (`page_id`, `module`) VALUES
(2, 'menu_v_root'),
(1, 'content_v_index_a'),
(1, 'menu_h_root'),
(4, 'menu_v_root'),
(3, 'menu_v_root');

-- --------------------------------------------------------

--
-- Структура таблицы `def_page_types`
--

CREATE TABLE IF NOT EXISTS `def_page_types` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Структура таблицы `def_section`
--

CREATE TABLE IF NOT EXISTS `def_section` (
  `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `prio` tinyint(4) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `url` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `redirect` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `admin_id` smallint(1) unsigned NOT NULL,
  `contacts` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contacts2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contacts3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Дамп данных таблицы `def_section`
--

INSERT INTO `def_section` (`id`, `prio`, `name`, `url`, `redirect`, `admin_id`, `contacts`, `contacts2`, `contacts3`) VALUES
(1, 0, 'Сайт', '', '', 0, '', '', '');

-- --------------------------------------------------------

--
-- Структура таблицы `def_settings`
--

CREATE TABLE IF NOT EXISTS `def_settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ro` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `section` (`section`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

--
-- Дамп данных таблицы `def_settings`
--

INSERT INTO `def_settings` (`id`, `section`, `name`, `value`, `title`, `ro`) VALUES
(1, 1, 'content_email', 'info@fonbrand.com', 'Эл. адрес', 0),
(2, 1, 'site_name', 'новый сайт', 'Название сайта', 0),
(3, 1, 'content_tel', '(044) 222-55-04', 'Телефон', 0),
(4, 1, 'site_index_redirect', '', 'Перенаправление с первой страницы', 0),
(5, 1, 'site_use_section', '0', 'Структура. Использовать секции?', 1),
(6, 1, 'twitter', 'http://twitter.com/', 'Ссылка на твиттер', 0),
(7, 1, 'facebook', 'http://facebook.com/', 'Ссылка на фейсбук', 0),
(8, 1, 'vkontakte', 'http://vk.com/', 'Ссылка вконтакте', 0),
(9, 1, 'sms_telephones', '', 'Телефоны для смс рассылки', 0),
(10, 1, 'sms_gw_sender', '', 'Подпись смс', 0),
(11, 1, 'sms_balance', '1000', 'Смс баланс', 1),
(12, 1, 'sms_gw_login', 'login', 'Смс логин', 0),
(13, 1, 'sms_gw_pass', 'pass', 'Смс пароль', 0),
(14, 1, 'sms_gw_url', 'http://turbosms.in.ua/api/wsdl.html', 'Смс шлюз', 0),
(15, 1, 'style_color', 'default', 'Цветовая схема сайта', 0),
(16, 1, 'style_theme', 'default', 'Дизайн-тема сайта', 0);

-- --------------------------------------------------------

--
-- Структура таблицы `def_users`
--

CREATE TABLE IF NOT EXISTS `def_users` (
  `id` mediumint(9) NOT NULL AUTO_INCREMENT,
  `cid` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `state` tinyint(4) NOT NULL,
  `login` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `pass` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fio` varchar(500) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `avatar` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `uxtreg` char(10) COLLATE utf8_unicode_ci NOT NULL,
  `uxt` char(10) COLLATE utf8_unicode_ci NOT NULL,
  `is_adm` tinyint(4) NOT NULL,
  `is_1` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_2` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_3` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `is_4` tinyint(3) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cid` (`cid`),
  KEY `email_in` (`pass`,`email`),
  KEY `login` (`login`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Дамп данных таблицы `def_users`
--

INSERT INTO `def_users` (`id`, `cid`, `state`, `login`, `pass`, `email`, `fio`, `avatar`, `uxtreg`, `uxt`, `is_adm`, `is_1`, `is_2`, `is_3`, `is_4`) VALUES
(1, '837ec5754f503cfaaee0929fd48974e7270942e3b4c06ced22d26cc661415d28', 1, 'rage', 'rage', 'rage', '', '', '', '1364891823', 1, 0, 0, 0, 0),
(2, 'f528764d624db129b32c21fbca0cb8d6d57405496b45ac0f97e01d231e277518', 2, 'root', 'root', 'root', '', '', '', '1362558049', 1, 0, 0, 0, 0),
(3, '7eb7429bbf42e3cf3c9703f4d3d2c42435714b582ff23071ae11d1096ac45605', 2, 'wins', 'wins', 'wins', '', '', '', '1362553785', 1, 0, 0, 0, 0),
(4, '94eac188bbb9a6119d58f125dfad1c7a5b2f3581f21ab6302baa933808b65b5f', 2, 'fon', 'fon', 'fon', 'fon', '', '', '1360758918', 1, 0, 0, 0, 0),
(5, '2ccd77a8183d93a1c6c7bdb51619b92b97e519fd02d6e1e1116945701e50655c', 2, 'fon2', 'fon2', 'fon2', '', '', '', '1356426732', 1, 0, 0, 0, 0),
(6, '7f4059492761b86a112f8f5b5cdbd94c4ffd19e6a3de43f19ae7c9792766a904', 2, 'dobby', 'dobby', 'dobby', 'Лена', '', '', '1358773560', 1, 0, 0, 0, 0);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;