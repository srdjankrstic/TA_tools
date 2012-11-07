DELIMITER $$

CREATE DEFINER=`skrstic`@`localhost` PROCEDURE `make_list`(hw int)
begin
	select upper(x.pid) as 'PID', x.name as 'Name', x.Partner, x.Email, coalesce(y.Path, '') as 'Files'
	from	(
		select s1.pid, s1.id, s1.name, coalesce(s2.name, '') as 'Partner', s1.email as 'Email'
		from students s1
		left outer join students s2
		on s2.name = find_partner(s1.id, hw)
	) x
	left outer join (
		select r.studentid, r.homework, group_concat(f.path) as 'Path'
		from reviews r, files f
		where r.homework = hw - 1 and r.fileid = f.id
		group by r.studentid, r.homework
	) y
	on y.studentid = x.id
	where x.Name != 'Krstic, Srdjan'
	order by x.Name asc;
end$$



DELIMITER $$

CREATE DEFINER=`skrstic`@`localhost` PROCEDURE `make_list2`(hw int)
begin
	select upper(x.pid) as 'PID', x.name as 'Name', x.Partner, x.Email, coalesce(y.Path, '') as 'Files'
	from	(
		select s1.pid, s1.id, s1.name, coalesce(s2.name, '') as 'Partner', s1.email as 'Email'
		from students s1
		left outer join students s2
		on s2.name = find_partner2(s1.id, hw - 1)
	) x
	left outer join (
		select r.studentid, r.homework, group_concat(f.path) as 'Path'
		from reviews r, files f
		where r.homework = hw - 1 and r.fileid = f.id
		group by r.studentid, r.homework
	) y
	on y.studentid = x.id
	where x.Name != 'Krstic, Srdjan'
	order by x.Name asc;
end$$



DELIMITER $$

CREATE DEFINER=`root`@`localhost` FUNCTION `find_partner`(sid1 int, hw int) RETURNS varchar(100) CHARSET utf8
    DETERMINISTIC
begin
declare fid, sid2 int;
declare s2name varchar(100);
select fileId into fid from solutions where studentid = sid1 and homework = hw;
select studentId into sid2 from solutions where fileId = fid and homework = hw and studentId != sid1;
if sid2 is not null then
        select name into s2name from students where id = sid2;
     else
        set s2name = '';
     end if;
     return s2name;
end$$



DELIMITER $$

CREATE DEFINER=`root`@`localhost` FUNCTION `find_partner2`(sid1 int, hw int) RETURNS varchar(100) CHARSET utf8
    DETERMINISTIC
begin
declare fid, sid2 int;
declare s2name varchar(100);
select fileId into fid from reviews where studentid = sid1 and homework = hw;
select studentId into sid2 from reviews where fileId = fid and homework = hw and studentId != sid1;
if sid2 is not null then
        select name into s2name from students where id = sid2;
     else
        set s2name = '';
     end if;
     return s2name;
end$$
