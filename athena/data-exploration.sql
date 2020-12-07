
-- __________________________________________________________________________
-- Create Genre Map

select
     x.value genre,
     row_number() over (order by x.value) idx
from title_basics tbasics
join title_akas takas on tbasics.tconst = takas.titleid
left join title_ratings tratings on tbasics.tconst = tratings.tconst
cross join unnest(split(tbasics.genres, ',')) as x(value)
where tbasics.startyear <= year(now()) 
and tbasics.titleType = 'movie' 
and tbasics.isadult = 0
and takas.language = 'en'
and takas.region = 'US'
group by 1
order by 2;


-- __________________________________________________________________________
-- Find Top 10 Genres

select
     x.value, count(*) cnt
from title_basics tbasics
join title_akas takas on tbasics.tconst = takas.titleid
left join title_ratings tratings on tbasics.tconst = tratings.tconst
cross join unnest(split(tbasics.genres, ',')) as x(value)
where tbasics.startyear <= year(now()) 
and tbasics.titleType = 'movie' 
and tbasics.isadult = 0
and takas.language = 'en'
and takas.region = 'US'
group by 1
order by 2 desc
limit 10;


-- __________________________________________________________________________
-- Find Distinct Regions

select region, count(*) cnt from title_akas
group by 1
order by 1;

-- __________________________________________________________________________
-- Select Desired Format

select 
	res.*,
	contains(res.genres, 'Action') isaction,
	contains(res.genres, 'Adventure') isadventure,
	contains(res.genres, 'Comedy') iscomedy,
	contains(res.genres, 'Crime') iscrime,
	contains(res.genres, 'Documentary') isdocumentary,
	contains(res.genres, 'Drama') isdrama,
	contains(res.genres, 'Horror') ishorror,
	contains(res.genres, 'Mystery') ismystery,
	contains(res.genres, 'Romance') isromance,
	contains(res.genres, 'Thriller') isthriller
from (
select distinct 
	tbasics.tconst id,
	tbasics.primarytitle title,
	tbasics.startyear year,
	split(tbasics.genres, ',') genres,
	tratings.averagerating rating,
	tratings.numvotes votes
from title_basics tbasics
join title_akas takas on tbasics.tconst = takas.titleid
join title_ratings tratings on tbasics.tconst = tratings.tconst
where tbasics.startyear <= year(now()) 
and tbasics.titleType = 'movie' 
and tbasics.isadult = 0
and takas.language = 'en'
and takas.region = 'US'
) res;

-- __________________________________________________________________________
-- Count Records in Desired Format

select count(*) from (
select distinct 
	tbasics.tconst id,
	tbasics.primarytitle title,
	tbasics.startyear year,
	split(tbasics.genres, ',') genres,
	tratings.averagerating rating,
	tratings.numvotes votes
from title_basics tbasics
join title_akas takas on tbasics.tconst = takas.titleid
join title_ratings tratings on tbasics.tconst = tratings.tconst
where tbasics.startyear <= year(now()) 
and tbasics.titleType = 'movie' 
and tbasics.isadult = 0
and takas.language = 'en'
and takas.region = 'US'
) cnt;




