CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `tag` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

INSERT INTO `roles` (`name`, `tag`) VALUES
('Администратор', 'admin'),
('Модератор', 'editor'),
('Игрок', 'author');


CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `login` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `role_id` int(11) DEFAULT '3',
  `email` varchar(255) DEFAULT NULL,
  age int(11) not null,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

INSERT INTO `users` (`login`, `password`, `role_id`) VALUES
('admin', '$2y$10$ZPbyuHSy/eOgUhXr07fMCeRphu1qsJRRAB5ij9alZWSKM4r0TR1zW', 1);


CREATE TABLE IF NOT EXISTS `auth_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(32) CHARACTER SET utf8 NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `menus` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(100) DEFAULT NULL,
  `text` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `position` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

insert into menus (link, text, position) values
("/", "Игра", 1),
("/words", "Слова", 2);


CREATE TABLE IF NOT EXISTS `menu_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `menu_id` int(11) NOT NULL,
  `link` varchar(100) NOT NULL,
  `text` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `position` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(100) CHARACTER SET utf8 NOT NULL,
  `entity_id` int(11) NOT NULL,
  `tag` varchar(250) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `words` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `word` varchar(250) NOT NULL,
  language_id int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `word_bin` varchar(250) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  KEY created_by (created_by)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(250) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY created_by (created_by)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

INSERT INTO `languages` (`name`, `created_by`) VALUES
('Русский', 1);


CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  language_id int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` timestamp null default null,
  PRIMARY KEY (`id`),
  KEY user_id (user_id),
  KEY language_id (language_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


create table if not exists associations (
    id int(11) not null auto_increment,
    language_id int default null,
    first_word_id int not null,
    second_word_id int not null,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by int DEFAULT NULL,
    deleted_at timestamp null default null,
    deleted_by int DEFAULT NULL,
    PRIMARY KEY (id),
    KEY language_id (language_id),
    KEY first_word_id (first_word_id),
    KEY second_word_id (second_word_id),
    KEY created_by (created_by)
) engine=InnoDB default charset=utf8 collate=utf8_general_ci;


CREATE TABLE IF NOT EXISTS `turns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  language_id int NOT NULL,
  game_id int NOT NULL,
  `user_id` int,
  word_id int NOT NULL,
  association_id int,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `finished_at` timestamp null DEFAULT NULL,
  prev_turn_id int,
  PRIMARY KEY (`id`),
  KEY language_id (language_id),
  KEY game_id (game_id),
  KEY user_id (user_id),
  KEY word_id (word_id),
  KEY association_id (association_id),
  KEY prev_turn_id (prev_turn_id)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;


create table if not exists event_types (
    id int(11) not null auto_increment,
    name varchar(250) not null,
    PRIMARY KEY (id)
) engine=InnoDB default charset=utf8 collate=utf8_general_ci;


create table if not exists events (
    id int(11) not null auto_increment,
    type_id int not null,
    entity_type varchar(250) not null,
    entity_id int not null,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by int DEFAULT NULL,
    details varchar(1000),
    PRIMARY KEY (id),
    KEY type_id (type_id),
    KEY created_by (created_by)
) engine=InnoDB default charset=utf8 collate=utf8_general_ci;


create table if not exists word_feedbacks (
    id int(11) not null auto_increment,
    word_id int not null,
    dislike tinyint(1) not null default 0,
    typo varchar(250) default null,
    duplicate_id int default null,
    mature tinyint(1) not null default 0,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by int not null,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY word_id (word_id),
    KEY duplicate_id (duplicate_id),
    KEY created_by (created_by)
) engine=InnoDB default charset=utf8 collate=utf8_general_ci;


create table if not exists association_feedbacks (
    id int(11) not null auto_increment,
    association_id int not null,
    dislike tinyint(1) not null default 0,
    mature tinyint(1) not null default 0,
    created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    created_by int not null,
    updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY association_id (association_id),
    KEY created_by (created_by)
) engine=InnoDB default charset=utf8 collate=utf8_general_ci;
