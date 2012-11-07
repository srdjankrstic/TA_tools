delimiter $$

CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pid` (`pid`)
) ENGINE=InnoDB AUTO_INCREMENT=208 DEFAULT CHARSET=utf8$$

delimiter $$

CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=418 DEFAULT CHARSET=utf8$$

delimiter $$

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `studentId` int(11) NOT NULL,
  `homework` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_review_student` (`studentId`),
  KEY `fk_review_file` (`fileId`),
  CONSTRAINT `fk_review_file` FOREIGN KEY (`fileId`) REFERENCES `files` (`id`),
  CONSTRAINT `fk_review_student` FOREIGN KEY (`studentId`) REFERENCES `students` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1833 DEFAULT CHARSET=utf8$$

delimiter $$

CREATE TABLE `solutions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `homework` int(11) NOT NULL,
  `fileId` int(11) NOT NULL,
  `studentId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_solution_file` (`fileId`),
  KEY `fk_solution_student` (`studentId`),
  CONSTRAINT `fk_solution_file` FOREIGN KEY (`fileId`) REFERENCES `files` (`id`),
  CONSTRAINT `fk_solution_student` FOREIGN KEY (`studentId`) REFERENCES `students` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=836 DEFAULT CHARSET=utf8$$

