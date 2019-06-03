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
