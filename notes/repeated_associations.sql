select * from (

select association_id, count(*) count from (

SELECT distinct association_id, user_id FROM `turns` WHERE user_id is not null and association_id is not null
) select1

group by association_id
) select2

where count > 1
