--
-- PostgreSQL database dump
--

-- Dumped from database version 14.3 (Debian 14.3-1.pgdg110+1)
-- Dumped by pg_dump version 14.3 (Debian 14.3-1.pgdg110+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: facet; Type: TABLE DATA; Schema: resto; Owner: resto
--

COPY resto.facet (id, collection, value, type, pid, isleaf, counter, created, metadata, description, owner) FROM stdin;
state:Agstafa:828297	S2	Ağstafa	state	region:GanjaGazakhEconomicRegion:	1	1	2024-05-20 10:02:49.029034	\N	\N	100
state:CityOfHamilton:3573195	S2	City of Hamilton	state	country:Bermuda:3573345	1	1	2024-04-19 19:45:34.387207	\N	\N	100
state:TheQuarter:11205439	S2	The Quarter	state	country:Anguilla:3573512	1	6	2021-10-01 20:22:56.674758	\N	\N	100
state:Rubirizi:226674	S2	Rubirizi	state	region:Western:8260675	1	1	2024-05-06 12:02:09.507993	\N	\N	100
state:CityOfSaintGeorge:3573062	S2	City of Saint George	state	country:Bermuda:3573345	1	1	2024-04-19 19:45:34.39436	\N	\N	100
state:Dagdas:7628350	S2	Dagdas	state	region:Latgale:7639662	1	1	2024-04-22 15:19:55.004252	\N	\N	100
state:Kotido:230617	S2	Kotido	state	region:Northern:8260674	1	2	2022-05-04 13:35:10.05843	\N	\N	100
state:Badulla:1250614	S2	Badulla	state	region:UturumaĕDaPalata:	1	1	2024-06-17 09:07:32.032181	\N	\N	100
state:Rochdale:3333188	S2	Rochdale	state	region:NorthWest:2641227	1	1	2024-06-20 22:22:43.318231	\N	\N	100
state:NavassaIsland:4743755	S2	Navassa Island	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	487	2019-07-02 08:37:12.406008	\N	\N	100
country:Bahamas:3572887	S2	Bahamas	country	continent:NorthAmerica:6255149	0	537	2019-07-02 23:59:13.450901	\N	\N	100
state:SlovenskeKonjice:3190529	S2	Slovenske Konjice	state	region:Savinjska:3186550	1	1	2024-03-18 03:10:48.294443	\N	\N	100
state:Minnesota:5037779	S2	Minnesota	state	region:Midwest:11887750	1	30606	2019-06-30 10:15:59.245355	\N	\N	100
state:Ngiwal:4038179	S2	Ngiwal	state	country:Palau:1559581	1	491	2019-07-04 08:28:40.262158	\N	\N	100
state:Hamilton:3573195	S2	Hamilton	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.366825	\N	\N	100
state:Davao:7521309	S2	Davao	state	region:Davao(RegionXi):	1	9	2020-11-07 05:27:25.707849	\N	\N	100
state:Fria:2420883	S2	Fria	state	region:Boke:8335085	1	1	2024-01-11 15:16:13.965212	\N	\N	100
state:Zelino:863919	S2	Želino	state	region:Polog:786909	1	1	2023-08-19 17:49:37.892578	\N	\N	100
state:Chirang:1337281	S2	Chirang	state	region:Central:	1	1	2023-02-18 09:48:15.546273	\N	\N	100
state:Sentilj:3239214	S2	Šentilj	state	region:Podravska:	1	1	2023-04-20 17:57:21.040932	\N	\N	100
state:Kiboga:231550	S2	Kiboga	state	region:Central:234594	1	1	2024-03-14 13:49:33.339702	\N	\N	100
state:Krabi:1152631	S2	Krabi	state	region:Southern:10234924	1	11	2020-07-25 16:28:43.982533	\N	\N	100
region:NaegenahiraPalata:	S2	Næ̆gĕnahira paḷāta	region	country:SriLanka:1227603	0	301	2022-03-11 10:12:48.151967	\N	\N	100
state:Biliran:1725260	S2	Biliran	state	region:EasternVisayas(RegionViii):	1	16	2019-12-01 04:47:59.641655	\N	\N	100
state:AliSabieh:225282	S2	Ali Sabieh	state	country:Djibouti:223816	1	515	2019-06-30 20:44:14.374348	\N	\N	100
state:Mongar:1337285	S2	Mongar	state	region:Eastern:	1	9	2021-05-17 12:47:41.54715	\N	\N	100
state:SelebiPhikwe:933099	S2	Selebi-Phikwe	state	country:Botswana:933860	1	490	2019-07-04 14:17:21.286709	\N	\N	100
day:07	S2	07	day	root	1	931205	2019-07-07 03:49:21.310264	\N	\N	100
state:Kratovo:789090	S2	Kratovo	state	region:Northeastern:9072895	1	1	2023-08-29 19:41:54.646956	\N	\N	100
state:Dokolo:8658742	S2	Dokolo	state	region:Northern:8260674	1	1	2020-04-04 17:36:57.807825	\N	\N	100
state:Nzerekore:8335091	S2	Nzérékoré	state	region:Nzerekore:8335091	1	1	2023-03-21 18:02:32.990211	\N	\N	100
state:TienGiang:1564676	S2	Tiền Giang	state	region:DongBangSongCuuLong:1574717	1	2	2022-12-06 07:52:34.075576	\N	\N	100
state:Asti:3182712	S2	Asti	state	region:Piemonte:3170831	1	3	2022-09-11 18:05:47.214345	\N	\N	100
state:Bugiri:234565	S2	Bugiri	state	region:Eastern:8260673	1	2	2024-02-21 00:47:32.96579	\N	\N	100
state:Edinburgh:3333229	S2	Edinburgh	state	region:Eastern:2650458	1	1	2024-03-04 19:58:29.126043	\N	\N	100
landcover:cultivated	S2	Cultivated	landcover:cultivated	root	1	3841153	2019-06-30 09:52:01.251137	\N	\N	100
state:ZamboangaDelSur:1679428	S2	Zamboanga del Sur	state	region:NorthernMindanao(RegionX):	1	126	2022-05-19 07:53:08.679644	\N	\N	100
state:Nakaseke:8644153	S2	Nakaseke	state	region:Central:234594	1	3	2024-03-14 14:09:55.859076	\N	\N	100
state:Iecavas:7628321	S2	Iecavas	state	region:Zemgale:7639660	1	2	2024-03-11 22:17:49.818077	\N	\N	100
state:Zabul:121243	S2	Zabul	state	country:Afghanistan:1149361	1	7	2022-03-10 10:09:51.365035	\N	\N	100
state:Kaberamaido:8658290	S2	Kaberamaido	state	region:Eastern:8260673	1	1	2020-04-04 17:36:57.809511	\N	\N	100
state:Leitrim:2962975	S2	Leitrim	state	region:Border:	1	4	2021-09-05 19:00:10.748436	\N	\N	100
state:Riebinu:7628344	S2	Riebinu	state	region:Latgale:7639662	1	1	2024-03-27 06:41:48.728296	\N	\N	100
state:Kamwenge:232371	S2	Kamwenge	state	region:Western:8260675	1	2	2021-01-22 13:41:46.851896	\N	\N	100
state:VinhLong:1559977	S2	Vĩnh Long	state	region:DongBangSongCuuLong:1574717	1	1	2023-12-26 06:54:28.875538	\N	\N	100
state:Qax:586290	S2	Qax	state	region:ShakiZaqatalaEconomicRegion:	1	4	2020-03-22 23:17:36.81276	\N	\N	100
state:Padova:3171727	S2	Padova	state	region:Veneto:3164604	1	12	2021-01-08 15:05:55.266736	\N	\N	100
state:Vicenza:3164418	S2	Vicenza	state	region:Veneto:3164604	1	12	2021-01-08 13:51:14.753266	\N	\N	100
state:Kwara:2332785	S2	Kwara	state	country:Nigeria:2328926	1	4159	2019-06-30 17:58:18.312631	\N	\N	100
state:KeelungCity:1678228	S2	Keelung City	state	region:TaiwanProvince:1668284	1	3	2020-10-27 09:24:15.247034	\N	\N	100
state:Zombo:225797	S2	Zombo	state	region:Northern:8260674	1	3	2021-12-18 11:49:33.128594	\N	\N	100
state:Konce:862953	S2	Konče	state	region:Southeastern:9166199	1	1	2023-07-27 16:57:20.746707	\N	\N	100
state:Astara:148442	S2	Astara	state	region:LankaranEconomicRegion:	1	2	2023-09-02 14:39:33.212574	\N	\N	100
state:Newport:3333246	S2	Newport	state	region:WestWalesAndTheValleys:	1	1	2023-08-29 19:59:23.873013	\N	\N	100
state:GorenjaVasPoljane:3239095	S2	Gorenja vas-Poljane	state	region:Gorenjska:	1	1	2023-08-23 21:21:36.263534	\N	\N	100
state:Agdas:587376	S2	Ağdaş	state	region:AranEconomicRegion:	1	1	2023-09-10 14:18:08.013404	\N	\N	100
state:Gostivar:863855	S2	Gostivar	state	region:Polog:786909	1	1	2024-03-26 19:40:03.338541	\N	\N	100
state:SamutPrakan:1606589	S2	Samut Prakan	state	region:Central:10177180	1	4	2021-07-05 16:39:58.012761	\N	\N	100
state:BouvetIsland:	S2	Bouvet Island	state	country:Norway:3144096	1	234	2022-07-11 15:34:59.198937	\N	\N	100
state:Piacenza:3171057	S2	Piacenza	state	region:EmiliaRomagna:3177401	1	10	2020-02-03 16:14:42.077569	\N	\N	100
state:Donduseni:618381	S2	Donduseni	state	country:Moldova:617790	1	519	2019-06-30 19:04:46.042072	\N	\N	100
month:12	S2	12	month	root	1	1954387	2019-10-01 09:40:35.690352	\N	\N	100
state:Coleraine:2652587	S2	Coleraine	state	region:NorthernIreland:2635167	1	3	2020-04-10 19:36:03.635362	\N	\N	100
state:Madakalapuva:1250159	S2	Maḍakalapuva	state	region:NaĕGenahiraPalata:	1	6	2020-05-28 06:01:51.801922	\N	\N	100
country:Grenada:3580239	S2	Grenada	country	continent:NorthAmerica:6255149	0	1016	2019-07-02 00:16:17.382474	\N	\N	100
state:AlifuAlifu:1282497	S2	Alifu Alifu	state	region:NorthCentral:8030594	1	18	2020-04-26 10:57:14.489434	\N	\N	100
state:Sheffield:2638077	S2	Sheffield	state	region:YorkshireAndTheHumber:11591951	1	3	2020-03-30 19:59:33.737757	\N	\N	100
state:Bursa:750268	S2	Bursa	state	country:Turkey:298795	1	2592	2019-07-02 16:35:24.608153	\N	\N	100
state:Bejaia:2505325	S2	Béjaïa	state	country:Algeria:2589581	1	1532	2019-06-30 09:51:54.124131	\N	\N	100
state:HowlandIsland:5854922	S2	Howland Island	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	1	2023-06-23 04:28:55.275266	\N	\N	100
state:Oldham:3333179	S2	Oldham	state	region:NorthWest:2641227	1	1	2022-10-13 15:58:55.003039	\N	\N	100
state:Yonne:2967222	S2	Yonne	state	region:ChampagneArdenne:11071622	1	1	2022-12-16 16:48:15.411648	\N	\N	100
state:AlQadisiyyah:99022	S2	Al-Qādisiyyah	state	region:Iraq:99237	1	1	2023-05-18 13:55:30.022405	\N	\N	100
state:Novara:3172188	S2	Novara	state	region:Piemonte:3170831	1	1	2022-10-23 15:26:44.461588	\N	\N	100
state:BakerIsland:5854907	S2	Baker Island	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	1	2022-07-13 00:55:30.083589	\N	\N	100
state:IsleOfWight:2646007	S2	Isle of Wight	state	region:SouthEast:2637438	1	1	2023-05-31 20:35:47.618442	\N	\N	100
state:Agcabədi:148615	S2	Ağcabədi	state	region:AranEconomicRegion:	1	1	2023-06-22 13:58:52.153473	\N	\N	100
state:NorthSomerset:3333177	S2	North Somerset	state	region:SouthWest:11591956	1	14	2022-01-01 13:08:51.216588	\N	\N	100
state:Shaviyani:1281892	S2	Shaviyani	state	region:UpperNorth:8030591	1	556	2020-04-16 16:01:43.970867	\N	\N	100
state:Mislinja:3239207	S2	Mislinja	state	region:Koroska:3217862	1	507	2019-07-02 18:04:26.928538	\N	\N	100
state:Marinduque:1700902	S2	Marinduque	state	region:Mimaropa(RegionIvB):	1	12	2020-06-21 07:53:29.331732	\N	\N	100
state:Ravenna:3169560	S2	Ravenna	state	region:EmiliaRomagna:3177401	1	525	2019-06-30 18:17:41.000335	\N	\N	100
state:NongBuaLamPhu:1608268	S2	Nong Bua Lam Phu	state	region:Northeastern:	1	9	2020-06-15 11:19:47.906767	\N	\N	100
state:Klaipedos:864478	S2	Klaipedos	state	country:Lithuania:597427	1	2592	2019-06-30 18:17:43.753949	\N	\N	100
state:SanaA:71137	S2	Sana'a	state	country:Yemen:69543	1	2092	2019-06-30 19:32:50.912768	\N	\N	100
state:Karabuk:862468	S2	Karabük	state	country:Turkey:298795	1	511	2019-07-01 22:36:02.308574	\N	\N	100
state:Bari:64661	S2	Bari	state	country:Somalia:51537	1	6450	2019-07-01 19:22:50.029443	\N	\N	100
state:Ngaraard:4037962	S2	Ngaraard	state	country:Palau:1559581	1	490	2019-07-04 08:28:40.262613	\N	\N	100
state:BasCongo:2317277	S2	Bas-Congo	state	country:Congo:203312	1	2269	2022-05-05 14:20:46.637689	\N	\N	100
state:Sfax:2467450	S2	Sfax	state	country:Tunisia:2464461	1	1034	2019-06-30 18:08:10.40909	\N	\N	100
state:Sabirabad:147284	S2	Sabirabad	state	region:AranEconomicRegion:	1	18	2021-06-09 10:15:59.835457	\N	\N	100
state:Rhone:2987410	S2	Rhône	state	region:RhoneAlpes:11071625	1	10	2020-05-17 12:40:06.647283	\N	\N	100
state:Borgou:2394992	S2	Borgou	state	country:Benin:2395170	1	2960	2019-06-30 18:00:36.036829	\N	\N	100
state:Siliana:2465027	S2	Siliana	state	country:Tunisia:2464461	1	1745	2019-06-30 18:05:03.777001	\N	\N	100
region:UturumaedaPalata:	S2	Uturumæ̆da paḷāta	region	country:SriLanka:1227603	0	251	2022-03-11 10:48:26.718695	\N	\N	100
state:Kayunga:231696	S2	Kayunga	state	region:Central:234594	1	1	2024-03-24 10:48:41.443436	\N	\N	100
state:TerritoireDeBelfort:3033789	S2	Territoire de Belfort	state	region:FrancheComte:11071619	1	2	2022-07-31 14:55:37.680358	\N	\N	100
state:Noonu:1281937	S2	Noonu	state	region:North:8030595	1	10	2020-01-22 07:55:44.126857	\N	\N	100
state:WongTaiSin:7533613	S2	Wong Tai Sin	state	region:Kowloon:1819609	1	6	2022-03-13 10:39:19.829581	\N	\N	100
state:Darlington:3333141	S2	Darlington	state	region:NorthEast:11591950	1	1	2023-11-27 19:52:51.104597	\N	\N	100
state:SandyGround:3573407	S2	Sandy Ground	state	country:Anguilla:3573512	1	6	2021-10-01 20:22:56.67583	\N	\N	100
state:Surrey:2636512	S2	Surrey	state	region:SouthEast:2637438	1	4	2021-08-11 13:56:02.582821	\N	\N	100
state:Muramvya:425550	S2	Muramvya	state	country:Burundi:433561	1	1031	2019-07-02 15:49:37.401862	\N	\N	100
state:Bam:2362973	S2	Bam	state	region:CentreNord:6930706	1	542	2019-06-30 09:51:48.688124	\N	\N	100
state:Trebnje:3188885	S2	Trebnje	state	region:JugovzhodnaSlovenija:	1	2	2023-08-23 21:27:52.609494	\N	\N	100
state:SaintAndrew:3373580	S2	Saint Andrew	state	country:Barbados:3374084	1	997	2019-07-03 22:09:43.470499	\N	\N	100
region:West:8642922	S2	West	region	country:Ireland:2963597	0	2277	2019-06-30 21:36:47.728307	\N	\N	100
state:Tacuarembo:3440033	S2	Tacuarembó	state	country:Uruguay:3439705	1	1133	2019-06-30 22:29:19.1651	\N	\N	100
state:DemeraraMahaica:3378741	S2	Demerara-Mahaica	state	country:Guyana:3378535	1	503	2019-06-30 23:44:35.694476	\N	\N	100
state:Siparia:7521945	S2	Siparia	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.028638	\N	\N	100
state:Ogun:2327546	S2	Ogun	state	country:Nigeria:2328926	1	2072	2019-06-30 18:00:20.975348	\N	\N	100
state:AscoliPiceno:3182748	S2	Ascoli Piceno	state	region:Marche:3174004	1	4	2021-06-26 05:17:10.54304	\N	\N	100
state:Vaduz:3042031	S2	Vaduz	state	country:Liechtenstein:3042058	1	502	2019-07-02 01:26:58.302153	\N	\N	100
state:Nakapiripirit:448226	S2	Nakapiripirit	state	region:Northern:8260674	1	517	2019-07-01 17:10:34.091948	\N	\N	100
state:Para:3383337	S2	Para	state	country:Suriname:3382998	1	2462	2019-06-30 10:10:21.807418	\N	\N	100
state:Plateaux:2255422	S2	Plateaux	state	country:Congo:203312	1	313	2022-05-07 16:06:40.357224	\N	\N	100
state:Kouilou:2258738	S2	Kouilou	state	country:Congo:203312	1	309	2022-05-08 15:14:28.079106	\N	\N	100
state:Berovo:863485	S2	Berovo	state	region:Southeastern:9166199	1	510	2019-07-03 17:05:36.391715	\N	\N	100
state:MouHoun:6930701	S2	Mou Houn	state	region:BoucleDuMouhoun:6930701	1	895	2019-06-30 09:51:47.651371	\N	\N	100
state:Komoe:2359209	S2	Komoé	state	region:Cascades:6930703	1	2094	2019-06-30 09:51:46.514595	\N	\N	100
state:Kyustendil:864554	S2	Kyustendil	state	country:Bulgaria:732800	1	1020	2019-07-03 17:01:40.888695	\N	\N	100
state:TainanCity:1668355	S2	Tainan City	state	region:SpecialMunicipalities:	1	10	2020-02-05 06:40:40.292648	\N	\N	100
state:Postojna:3192672	S2	Postojna	state	region:NotranjskoKraska:	1	2	2020-10-22 13:09:21.054978	\N	\N	100
state:Halton:2647601	S2	Halton	state	region:NorthWest:2641227	1	1	2022-07-18 19:44:45.824326	\N	\N	100
state:Xocali:148449	S2	Xocalı	state	region:YukhariGarabakhEconomicRegion:	1	1	2023-06-22 13:58:52.158102	\N	\N	100
state:Rotherham:3333189	S2	Rotherham	state	region:YorkshireAndTheHumber:11591951	1	1	2022-10-13 18:49:18.263387	\N	\N	100
year:2018	S2	2018	year	root	1	1367415	2019-08-06 11:12:03.774547	\N	\N	100
state:AnNuqatAlKhams:2593778	S2	An Nuqat al Khams	state	country:Libya:2215636	1	1539	2019-06-30 18:20:58.768649	\N	\N	100
state:Lubusz:3337494	S2	Lubusz	state	country:Poland:798544	1	3184	2019-06-30 18:00:59.632944	\N	\N	100
state:Devonshire:3573251	S2	Devonshire	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.367306	\N	\N	100
state:SouthDublin:7288565	S2	South Dublin	state	region:Dublin:2964573	1	5	2020-05-10 15:57:22.125351	\N	\N	100
country:PitcairnIslands:4030699	S2	Pitcairn Islands	country	continent:Oceania:6255151	0	492	2019-07-01 04:44:27.732569	\N	\N	100
state:Wanica:3382761	S2	Wanica	state	country:Suriname:3382998	1	989	2019-06-30 10:10:21.807043	\N	\N	100
state:Tochigi:1850310	S2	Tochigi	state	region:Kanto:1860102	1	48	2021-03-08 05:16:55.579959	\N	\N	100
state:AustralianCapitalTerritory:2172517	S2	Australian Capital Territory	state	country:Australia:2077456	1	671	2019-06-30 13:49:46.892343	\N	\N	100
region:Centre:6930704	S2	Centre	region	country:BurkinaFaso:2361809	0	1022	2019-06-30 09:51:49.26543	\N	\N	100
region:FriuliVeneziaGiulia:3176525	S2	Friuli-Venezia Giulia	region	country:Italy:3175395	0	3050	2019-06-30 18:19:31.882195	\N	\N	100
state:BjelovarskoBilogorska:3337511	S2	Bjelovarsko-bilogorska	state	country:Croatia:3202326	1	515	2019-07-02 17:55:30.726713	\N	\N	100
state:Hamerkaz:294904	S2	HaMerkaz	state	country:Israel:294640	1	516	2019-06-30 19:21:47.328702	\N	\N	100
state:LosLagos:3881974	S2	Los Lagos	state	country:Chile:3895114	1	11170	2019-07-01 01:19:03.219419	\N	\N	100
state:Ngatpang:1559532	S2	Ngatpang	state	country:Palau:1559581	1	492	2019-07-04 08:28:40.264048	\N	\N	100
state:MedioCampidano:6457402	S2	Medio Campidano	state	region:Sardegna:2523228	1	15	2020-02-08 12:21:49.920944	\N	\N	100
state:Edinet:617077	S2	Edineţ	state	country:Moldova:617790	1	1026	2019-06-30 19:04:46.042527	\N	\N	100
state:Kasserine:2473460	S2	Kassérine	state	country:Tunisia:2464461	1	1019	2019-07-03 17:44:47.608441	\N	\N	100
state:Misiones:3437727	S2	Misiones	state	country:Paraguay:3437598	1	1014	2019-07-03 21:17:09.504306	\N	\N	100
state:Brazzaville:2572183	S2	Brazzaville	state	country:Congo:2260494	1	630	2022-05-07 16:04:05.077318	\N	\N	100
state:Bubanza:428514	S2	Bubanza	state	country:Burundi:433561	1	516	2019-07-02 15:49:37.402863	\N	\N	100
state:PhuYen:1569805	S2	Phú Yên	state	region:NamTrungBo:11497252	1	1023	2019-07-02 12:32:50.86177	\N	\N	100
state:GrosIslet:3576685	S2	Gros Islet	state	country:SaintLucia:3576468	1	501	2019-07-03 21:43:53.532335	\N	\N	100
state:Swansea:2636432	S2	Swansea	state	region:EastWales:7302126	1	1	2024-02-22 02:06:10.861028	\N	\N	100
sea:MediterraneanSea:9293489	S2	Mediterranean Sea	sea	root	1	109	2022-03-10 12:55:18.736993	\N	\N	100
state:Enfield:3333146	S2	Enfield	state	region:GreaterLondon:2648110	1	1	2020-03-03 01:14:22.27179	\N	\N	100
state:Buckinghamshire:2654408	S2	Buckinghamshire	state	region:SouthEast:2637438	1	1	2020-03-03 01:14:22.274171	\N	\N	100
state:EastSussex:2650328	S2	East Sussex	state	region:SouthEast:2637438	1	9	2021-08-11 16:00:14.294493	\N	\N	100
state:Cavite:1717639	S2	Cavite	state	region:Calabarzon(RegionIvA):	1	19	2019-12-14 06:16:19.303448	\N	\N	100
state:Catanzaro:3170025	S2	Catanzaro	state	region:Calabria:2525468	1	11	2020-06-17 03:06:01.296045	\N	\N	100
state:Ballymena:2656491	S2	Ballymena	state	region:NorthernIreland:2635167	1	11	2020-04-10 19:41:25.982559	\N	\N	100
country:Barbados:3374084	S2	Barbados	country	continent:NorthAmerica:6255149	0	999	2019-07-03 22:09:43.473772	\N	\N	100
state:Gevgelija:863854	S2	Gevgelija	state	country:Macedonia:718075	1	509	2019-07-03 17:09:31.444556	\N	\N	100
state:Larne:2644850	S2	Larne	state	region:NorthernIreland:2635167	1	14	2020-04-10 19:37:52.331825	\N	\N	100
platform:S2B	S2	S2B	platform	root	0	15081162	2019-06-30 09:52:29.822292	\N	\N	100
state:Erevan:616051	S2	Erevan	state	country:Armenia:174982	1	496	2019-07-01 16:19:50.462712	\N	\N	100
collection:S2	S2	S2	collection	root	1	28594057	2019-06-30 09:51:38.326783	\N	\N	100
state:Marowijne:3383560	S2	Marowijne	state	country:Suriname:3382998	1	993	2019-06-30 10:10:32.338336	\N	\N	100
state:Ashanti:2304116	S2	Ashanti	state	country:Ghana:2300660	1	3129	2019-07-02 00:26:16.636765	\N	\N	100
state:JarvisIsland:5854926	S2	Jarvis Island	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	135	2022-06-07 02:56:39.968917	\N	\N	100
state:SaintJohn:3576023	S2	Saint John	state	country:AntiguaAndBarbuda:3576396	1	1029	2019-07-02 00:13:30.788689	\N	\N	100
state:Abim:235574	S2	Abim	state	region:Northern:8260674	1	5	2020-11-05 18:55:20.238021	\N	\N	100
state:Neretas:7628334	S2	Neretas	state	region:Zemgale:7639660	1	2	2024-02-21 20:15:10.705826	\N	\N	100
state:Sevastopol:496443	S2	Sevastopol	state	region:Volga:11961325	1	2	2020-07-25 18:57:23.359534	\N	\N	100
state:Adygey:584222	S2	Adygey	state	region:Volga:11961325	1	525	2019-07-02 17:04:39.480509	\N	\N	100
state:Benevento:3182178	S2	Benevento	state	region:Campania:3181042	1	6	2020-04-17 17:54:57.94806	\N	\N	100
state:Yaroslavl:468898	S2	Yaroslavl'	state	region:Northwestern:11961321	1	2	2023-01-09 12:14:07.992834	\N	\N	100
state:Hsinchu:1675107	S2	Hsinchu	state	region:TaiwanProvince:1668284	1	5	2020-03-31 09:24:40.509801	\N	\N	100
state:Akita:2113124	S2	Akita	state	region:Tohoku:2110769	1	1056	2019-06-30 10:14:14.735106	\N	\N	100
state:Likouala:2258431	S2	Likouala	state	country:Congo:203312	1	959	2022-05-07 16:02:52.764979	\N	\N	100
state:Agago:235460	S2	Agago	state	region:Northern:8260674	1	7	2020-11-05 11:45:50.998107	\N	\N	100
state:TraVinh:1559975	S2	Trà Vinh	state	region:DongBangSongCuuLong:1574717	1	911	2019-06-30 12:44:14.349063	\N	\N	100
state:Bundibugyo:234178	S2	Bundibugyo	state	region:Western:8260675	1	1	2023-07-11 15:39:42.045199	\N	\N	100
state:Kamuli:232397	S2	Kamuli	state	region:Eastern:8260673	1	3	2024-03-24 10:48:41.454442	\N	\N	100
state:Olongapo:1697172	S2	Olongapo	state	region:CentralLuzon(RegionIii):	1	1	2023-01-10 08:59:17.328821	\N	\N	100
state:Siguldas:7628362	S2	Siguldas	state	region:Riga:456173	1	4	2019-06-30 10:10:35.679188	\N	\N	100
state:HaNam:1905637	S2	Hà Nam	state	region:DongBangSongHong:	1	3	2020-01-24 07:58:22.999908	\N	\N	100
state:Riga:456173	S2	Riga	state	region:Riga:456173	1	1	2024-02-28 16:37:00.67573	\N	\N	100
state:Kirklees:3333161	S2	Kirklees	state	region:YorkshireAndTheHumber:11591951	1	2	2024-03-03 10:50:04.017083	\N	\N	100
state:Savanne:934017	S2	Savanne	state	country:Mauritius:934292	1	486	2019-07-03 13:42:31.758427	\N	\N	100
state:Ekiti:2595346	S2	Ekiti	state	country:Nigeria:2328926	1	1034	2019-06-30 18:00:11.984918	\N	\N	100
state:Sorsogon:1685754	S2	Sorsogon	state	region:Bicol(RegionV):	1	517	2019-06-30 14:09:49.755454	\N	\N	100
state:SaintGeorges:3578044	S2	Saint Georges	state	country:Montserrat:3578097	1	514	2019-07-02 00:09:40.297004	\N	\N	100
landcover:water	S2	Water	landcover:water	root	1	11824808	2019-06-30 09:51:43.54031	\N	\N	100
state:CouvaTabaquiteTalparo:7521938	S2	Couva-Tabaquite-Talparo	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.030928	\N	\N	100
location:northern	S2	Northern	location	root	1	20972595	2019-06-30 09:51:43.539789	\N	\N	100
state:Rotuma:6324593	S2	Rotuma	state	country:Fiji:2205218	1	530	2022-06-16 03:13:56.807403	\N	\N	100
state:Riscani:617483	S2	Rîşcani	state	country:Moldova:617790	1	563	2019-06-30 19:04:46.042989	\N	\N	100
state:KermadecIslands:4032961	S2	Kermadec Islands	state	region:NewZealandOutlyingIslands:	1	129	2022-06-17 00:52:48.764352	\N	\N	100
region:Pomurska:	S2	Pomurska	region	country:Slovenia:3190538	0	769	2019-06-30 18:07:32.984076	\N	\N	100
state:Mwaro:434386	S2	Mwaro	state	country:Burundi:433561	1	1152	2019-07-02 15:49:37.40135	\N	\N	100
state:Pader:448227	S2	Pader	state	region:Northern:8260674	1	9	2020-11-05 18:55:32.167774	\N	\N	100
sea:SargassoSea:3373404	S2	Sargasso Sea	sea	root	1	10508	2019-10-23 21:15:35.976731	\N	\N	100
state:SouthWest:7535957	S2	South West	state	country:Singapore:1880251	1	501	2019-06-30 12:41:03.837698	\N	\N	100
state:Shkoder:3344950	S2	Shkodër	state	country:Albania:783754	1	2049	2019-07-01 18:30:28.945044	\N	\N	100
state:Doncaster:3333143	S2	Doncaster	state	region:YorkshireAndTheHumber:11591951	1	2	2024-03-15 03:49:52.344014	\N	\N	100
state:Bolu:750510	S2	Bolu	state	country:Turkey:298795	1	3586	2019-07-01 22:20:44.996651	\N	\N	100
state:Ngardmau:4038043	S2	Ngardmau	state	country:Palau:1559581	1	490	2019-07-04 08:28:40.263526	\N	\N	100
country:IndianOceanTerritories:	S2	Indian Ocean Territories	country	continent:Asia:6255147	0	490	2019-07-04 09:32:51.876544	\N	\N	100
state:Ballymoney:2656489	S2	Ballymoney	state	region:NorthernIreland:2635167	1	4	2020-08-20 20:52:30.973069	\N	\N	100
state:Kadiogo:2359923	S2	Kadiogo	state	region:Centre:6930704	1	1022	2019-06-30 09:51:49.264645	\N	\N	100
state:Kerouane:2419618	S2	Kérouané	state	region:Kankan:8335087	1	514	2019-06-30 21:58:25.398053	\N	\N	100
gulf:GulfOfKamchatka:2125068	S2	Gulf of Kamchatka	gulf	root	1	3633	2019-07-01 09:30:39.458896	\N	\N	100
state:Usulutan:3582882	S2	Usulután	state	country:ElSalvador:3585968	1	1062	2019-07-02 02:14:25.375446	\N	\N	100
state:ZamboangaDelSur:1679428	S2	Zamboanga del Sur	state	region:ZamboangaPeninsula(RegionIx):	1	515	2019-06-30 14:15:23.420407	\N	\N	100
state:Ararat:409313	S2	Ararat	state	country:Armenia:174982	1	496	2019-07-01 16:19:50.464059	\N	\N	100
state:Lakshadweep:1265206	S2	Lakshadweep	state	region:South:8335041	1	269	2021-06-13 08:38:01.073008	\N	\N	100
state:SouthernLeyte:1685725	S2	Southern Leyte	state	region:EasternVisayas(RegionViii):	1	516	2019-07-01 10:40:56.642243	\N	\N	100
state:Charlotte:3577934	S2	Charlotte	state	country:SaintVincentAndTheGrenadines:3577815	1	998	2019-07-03 22:23:42.910063	\N	\N	100
state:BorgoMaggiore:3345304	S2	Borgo Maggiore	state	country:SanMarino:3345302	1	1017	2019-06-30 18:01:32.860183	\N	\N	100
state:Castries:3576810	S2	Castries	state	country:SaintLucia:3576468	1	499	2019-07-03 21:43:53.534426	\N	\N	100
state:Chiba:2113014	S2	Chiba	state	region:Kanto:1860102	1	1013	2019-06-30 10:10:42.687365	\N	\N	100
state:Bournemouth:3333129	S2	Bournemouth	state	region:SouthEast:2637438	1	2	2020-06-03 21:00:50.483011	\N	\N	100
state:Poole:3333182	S2	Poole	state	region:SouthEast:2637438	1	2	2020-06-03 21:00:50.484066	\N	\N	100
productType:REFLECTANCE	S2	REFLECTANCE	productType	root	1	28594094	2019-06-30 09:51:38.326393	\N	\N	100
gulf:JosephBonaparteGulf:2068951	S2	Joseph Bonaparte Gulf	gulf	root	1	3786	2019-06-30 10:10:32.007435	\N	\N	100
state:Maseru:932506	S2	Maseru	state	country:Lesotho:932692	1	571	2019-07-01 17:19:18.308992	\N	\N	100
state:Podvelka:3344916	S2	Podvelka	state	region:Koroska:3217862	1	9	2020-02-12 13:37:25.249769	\N	\N	100
state:Aimeliik:1559964	S2	Aimeliik	state	country:Palau:1559581	1	492	2019-07-04 08:28:40.264489	\N	\N	100
country:Palau:1559581	S2	Palau	country	continent:Oceania:6255151	0	493	2019-07-04 08:28:40.266332	\N	\N	100
gulf:GulfOfPapua:2088631	S2	Gulf of Papua	gulf	root	1	6059	2019-06-30 20:06:27.72062	\N	\N	100
month:05	S2	05	month	root	1	2827845	2019-09-05 14:27:26.190625	\N	\N	100
state:Perak:1733041	S2	Perak	state	country:Malaysia:1733045	1	2060	2019-07-03 11:13:57.105915	\N	\N	100
state:Soufriere:3576441	S2	Soufrière	state	country:SaintLucia:3576468	1	995	2019-07-03 21:43:53.534844	\N	\N	100
state:Bandundu:2317396	S2	Bandundu	state	country:Congo:2260494	1	1246	2022-05-07 16:03:42.177249	\N	\N	100
state:BathAndNorthEastSomerset:3333123	S2	Bath and North East Somerset	state	region:SouthEast:2637438	1	218	2022-01-29 15:27:33.985125	\N	\N	100
state:Warwick:3572972	S2	Warwick	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.367753	\N	\N	100
state:Butuan:1722183	S2	Butuan	state	region:DinagatIslands(RegionXiii):	1	1	2024-03-12 14:48:15.702194	\N	\N	100
state:Kostel:3197942	S2	Kostel	state	region:JugovzhodnaSlovenija:	1	2	2020-05-02 15:38:44.179505	\N	\N	100
country:SanMarino:3345302	S2	San Marino	country	continent:Europe:6255148	0	1017	2019-06-30 18:01:32.860777	\N	\N	100
state:ForliCesena:3176745	S2	Forlì-Cesena	state	region:EmiliaRomagna:3177401	1	1025	2019-06-30 18:01:32.861442	\N	\N	100
state:Kaffrine:2250804	S2	Kaffrine	state	country:Senegal:2245662	1	1135	2019-07-04 17:50:06.838585	\N	\N	100
state:Sinoe:2273898	S2	Sinoe	state	country:Liberia:2275384	1	1992	2019-06-30 21:57:30.293674	\N	\N	100
state:Collines:2597272	S2	Collines	state	country:Benin:2395170	1	2000	2019-07-03 19:39:13.217823	\N	\N	100
year:2021	S2	2021	year	root	1	9250403	2021-01-01 02:48:44.242723	\N	\N	100
state:Haifa:294800	S2	Haifa	state	country:Israel:294640	1	1020	2019-06-30 19:09:20.202865	\N	\N	100
state:Yasothon:1604767	S2	Yasothon	state	region:Northeastern:	1	3	2023-08-21 10:37:32.785764	\N	\N	100
state:AnseLaRaye:3576891	S2	Anse-la-Raye	state	country:SaintLucia:3576468	1	499	2019-07-03 21:43:53.533999	\N	\N	100
state:Pool:2255404	S2	Pool	state	country:Congo:203312	1	634	2022-05-05 14:21:23.547196	\N	\N	100
state:Gipuzkoa:3120935	S2	Gipuzkoa	state	region:PaisVasco:3336903	1	4	2022-02-02 15:21:49.884734	\N	\N	100
state:Brvenica:863835	S2	Brvenica	state	region:Polog:786909	1	1	2023-08-19 17:49:39.152421	\N	\N	100
state:NorthLincolnshire:3333176	S2	North Lincolnshire	state	region:YorkshireAndTheHumber:11591951	1	1	2024-03-03 11:37:54.986495	\N	\N	100
state:Niari:2256175	S2	Niari	state	country:Congo:2260494	1	2509	2022-05-05 14:21:37.902667	\N	\N	100
state:EastLothian:2650386	S2	East Lothian	state	region:Eastern:2650458	1	9	2020-06-18 20:55:24.590559	\N	\N	100
state:Paramaribo:3383329	S2	Paramaribo	state	country:Suriname:3382998	1	986	2019-06-30 10:10:21.806635	\N	\N	100
state:Hazafon:294824	S2	HaZafon	state	country:Israel:294640	1	511	2019-06-30 19:10:22.778451	\N	\N	100
state:ThaiBinh:1566338	S2	Thái Bình	state	region:DongBangSongHong:	1	500	2019-06-30 12:41:09.689441	\N	\N	100
state:CanTho:1581188	S2	Can Tho	state	country:Vietnam:1562822	1	498	2019-06-30 13:01:11.03577	\N	\N	100
state:SaintGeorgeS:3573057	S2	Saint George's	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.368352	\N	\N	100
state:Ngchesar:4037976	S2	Ngchesar	state	country:Palau:1559581	1	492	2019-07-04 08:28:40.263067	\N	\N	100
state:Ngeremlengui:4038068	S2	Ngeremlengui	state	country:Palau:1559581	1	491	2019-07-04 08:28:40.26588	\N	\N	100
state:DaNang:1905468	S2	Đà Nẵng	state	region:NamTrungBo:11497252	1	873	2019-06-30 12:45:19.184652	\N	\N	100
continent:NorthAmerica:6255149	S2	North America	continent	root	0	5368524	2019-06-30 09:52:23.688265	\N	\N	100
state:SouthernHighlands:2086331	S2	Southern Highlands	state	country:PapuaNewGuinea:2088628	1	4045	2019-06-30 20:06:25.90603	\N	\N	100
state:Yilan:1674197	S2	Yilan	state	region:TaiwanProvince:1668284	1	495	2019-06-30 11:03:24.524103	\N	\N	100
state:Thyolo:930283	S2	Thyolo	state	region:Southern:923817	1	11	2021-02-07 12:18:43.539579	\N	\N	100
state:LogoneOriental:2429058	S2	Logone Oriental	state	country:Chad:2434508	1	2601	2019-06-30 17:59:55.576	\N	\N	100
state:UpperWest:2294286	S2	Upper West	state	country:Ghana:2300660	1	1776	2019-06-30 09:51:48.318258	\N	\N	100
state:Saba:7610358	S2	Saba	state	country:Netherlands:2750405	1	504	2019-06-30 10:14:24.000815	\N	\N	100
state:Suceava:665849	S2	Suceava	state	country:Romania:798549	1	2071	2019-06-30 18:44:35.488214	\N	\N	100
state:Yamanashi:1848649	S2	Yamanashi	state	region:Chubu:1864496	1	509	2019-06-30 10:14:48.144103	\N	\N	100
state:Saitama:1853226	S2	Saitama	state	region:Kanto:1860102	1	540	2019-06-30 10:14:48.150454	\N	\N	100
state:Banwa:2597250	S2	Banwa	state	region:BoucleDuMouhoun:6930701	1	1171	2019-06-30 09:51:46.686838	\N	\N	100
state:Bong:2278292	S2	Bong	state	country:Liberia:2275384	1	1678	2019-06-30 22:17:59.569382	\N	\N	100
state:Dowa:929975	S2	Dowa	state	region:Central:931597	1	3	2019-12-20 11:54:56.23166	\N	\N	100
state:Pukapuka:4035563	S2	Pukapuka	state	country:CookIslands:1899402	1	264	2022-06-18 00:00:41.954775	\N	\N	100
state:Jerusalem:293198	S2	Jerusalem	state	country:Israel:294640	1	514	2019-06-30 19:21:47.328259	\N	\N	100
country:TrinidadAndTobago:3573591	S2	Trinidad and Tobago	country	continent:NorthAmerica:6255149	0	996	2019-07-03 22:21:39.031407	\N	\N	100
region:Western:8260675	S2	Western	region	country:Uganda:226074	0	6362	2019-07-02 15:32:40.756333	\N	\N	100
state:Albay:1731616	S2	Albay	state	region:Bicol(RegionV):	1	508	2019-06-30 14:14:42.675457	\N	\N	100
processingLevel:LEVEL1C	S2	LEVEL1C	processingLevel	root	1	28594132	2019-06-30 09:51:38.325997	\N	\N	100
state:Kotayk:828262	S2	Kotayk	state	country:Armenia:174982	1	495	2019-07-01 16:19:50.464483	\N	\N	100
state:SantaElena:7062138	S2	Santa Elena	state	country:Ecuador:3658394	1	519	2019-06-30 10:14:46.959044	\N	\N	100
state:ZamoraChinchipe:3649953	S2	Zamora Chinchipe	state	country:Ecuador:3658394	1	534	2019-07-02 08:40:58.794033	\N	\N	100
state:Zurrieq:8299766	S2	Żurrieq	state	region:MaltaXlokk:	1	1028	2019-07-02 17:58:59.512199	\N	\N	100
region:ChathamIslands:4033013	S2	Chatham Islands	region	country:NewZealand:2186224	0	966	2019-07-02 08:07:34.199561	\N	\N	100
state:UmmAlQaywayn:290595	S2	Umm Al Qaywayn	state	country:UnitedArabEmirates:290557	1	493	2019-07-03 14:55:43.724126	\N	\N	100
state:RiverCess:2588492	S2	River Cess	state	country:Liberia:2275384	1	508	2019-06-30 21:57:30.294657	\N	\N	100
state:AnNabatiyah:278915	S2	An Nabatiyah	state	country:Lebanon:272103	1	1018	2019-06-30 19:10:22.775306	\N	\N	100
country:Dominica:3575830	S2	Dominica	country	continent:NorthAmerica:6255149	0	1032	2019-07-02 00:12:17.353017	\N	\N	100
state:Ibaraki:2112669	S2	Ibaraki	state	region:Kanto:1860102	1	1039	2019-06-30 10:14:13.070276	\N	\N	100
state:Iwate:2112518	S2	Iwate	state	region:Tohoku:2110769	1	2031	2019-06-30 10:14:43.579988	\N	\N	100
state:IlesEparsesDeLOceanIndien:6690916	S2	Iles Eparses de l'ocean Indien	state	country:FrenchSouthernAndAntarcticLands:1546748	1	517	2019-07-04 14:44:08.717428	\N	\N	100
state:Yamagata:2110554	S2	Yamagata	state	region:Tohoku:2110769	1	1043	2019-06-30 10:13:56.779692	\N	\N	100
state:Siyəzən:828304	S2	Siyəzən	state	region:GubaKhachmazEconomicRegion:	1	2	2022-08-23 12:46:53.558011	\N	\N	100
state:Namutumba:8658444	S2	Namutumba	state	region:Eastern:8260673	1	1	2024-03-24 10:50:19.891349	\N	\N	100
state:LAquila:3175120	S2	L'Aquila	state	region:Abruzzo:3183560	1	1042	2019-06-30 18:18:12.820772	\N	\N	100
region:CentreNord:6930706	S2	Centre-Nord	region	country:BurkinaFaso:2361809	0	3666	2019-06-30 09:51:48.688873	\N	\N	100
state:Monastir:2473495	S2	Monastir	state	country:Tunisia:2464461	1	505	2019-06-30 18:04:04.162771	\N	\N	100
state:HautOgooue:2400454	S2	Haut-Ogooué	state	country:Gabon:2400553	1	4824	2019-06-30 18:39:49.348014	\N	\N	100
instrument:MSI	S2	MSI	instrument	platform:S2B	1	15081163	2022-05-04 17:10:55.184612	\N	\N	100
state:Kurunaegala:1237978	S2	Kuruṇægala	state	region:VayambaPalata:	1	535	2019-06-30 15:37:28.446726	\N	\N	100
state:BujumburaRural:7303940	S2	Bujumbura Rural	state	country:Burundi:433561	1	1195	2019-07-02 15:49:37.403813	\N	\N	100
state:Cartago:3624368	S2	Cartago	state	country:CostaRica:3624060	1	2043	2019-07-01 03:04:45.289806	\N	\N	100
country:Singapore:1880251	S2	Singapore	country	continent:Asia:6255147	0	501	2019-06-30 12:41:03.838257	\N	\N	100
state:Aragatsotn:828259	S2	Aragatsotn	state	country:Armenia:174982	1	1981	2019-07-01 16:19:50.464907	\N	\N	100
state:Manubah:2469273	S2	Manubah	state	country:Tunisia:2464461	1	1037	2019-06-30 18:05:03.777588	\N	\N	100
state:AzadKashmir:1184196	S2	Azad Kashmir	state	country:Pakistan:1168579	1	1085	2019-06-30 16:19:10.115736	\N	\N	100
country:Slovenia:3190538	S2	Slovenia	country	continent:Europe:6255148	0	3858	2019-06-30 18:02:41.807967	\N	\N	100
region:CanaryIs.:	S2	Canary Is.	region	country:Spain:2510769	0	1014	2019-06-30 21:28:00.991821	\N	\N	100
state:Medenine:2469470	S2	Médenine	state	country:Tunisia:2464461	1	2602	2019-06-30 18:17:12.555733	\N	\N	100
day:02	S2	02	day	root	1	933699	2019-07-02 08:54:31.083878	\N	\N	100
state:AntipodesIslands:	S2	Antipodes Islands	state	region:NewZealandOutlyingIslands:	1	540	2022-06-09 02:30:33.682075	\N	\N	100
state:Ghat:2217351	S2	Ghat	state	country:Libya:2215636	1	7214	2019-06-30 17:39:53.146237	\N	\N	100
state:Mchinji:926745	S2	Mchinji	state	region:Central:931597	1	1541	2019-07-01 17:03:32.740379	\N	\N	100
state:Pamplemousses:934212	S2	Pamplemousses	state	country:Mauritius:934292	1	484	2019-07-03 13:42:31.757107	\N	\N	100
state:Fiorentino:3345307	S2	Fiorentino	state	country:SanMarino:3345302	1	1015	2019-06-30 18:01:32.856433	\N	\N	100
state:Hiiu:592133	S2	Hiiu	state	country:Estonia:453733	1	2044	2019-06-30 18:03:43.576474	\N	\N	100
country:Ireland:2963597	S2	Ireland	country	continent:Europe:6255148	0	10579	2019-06-30 18:32:19.100108	\N	\N	100
state:RatakChain:2080009	S2	Ratak Chain	state	country:MarshallIslands:2080185	1	1004	2019-06-30 10:10:32.918533	\N	\N	100
state:Melekeok:4037930	S2	Melekeok	state	country:Palau:1559581	1	491	2019-07-04 08:28:40.261665	\N	\N	100
state:Salima:924053	S2	Salima	state	region:Central:931597	1	3	2019-12-20 11:56:11.640084	\N	\N	100
state:Ganzourgou:2360627	S2	Ganzourgou	state	region:PlateauCentral:6930712	1	503	2019-07-02 01:04:48.205387	\N	\N	100
state::	S2	_all	state	country:Kiribati:4030945	1	490	2022-05-07 03:44:53.981158	\N	\N	100
state:HauGiang:7506719	S2	Hau Giang	state	country:Vietnam:1562822	1	993	2019-06-30 12:45:48.901767	\N	\N	100
state:Solcava:3344895	S2	Solcava	state	region:Savinjska:3186550	1	512	2019-06-30 18:07:54.920887	\N	\N	100
state:SaintGeorgeBasseterre:3575180	S2	Saint George Basseterre	state	region:SaintKitts:3575174	1	516	2019-07-02 00:08:37.551143	\N	\N	100
state:SaintGeorge:3579926	S2	Saint George	state	country:Grenada:3580239	1	1016	2019-07-02 00:16:17.38201	\N	\N	100
state:Balvu:461160	S2	Balvu	state	region:Latgale:7639662	1	1019	2019-06-30 10:10:35.125777	\N	\N	100
state:Commewijne:3384418	S2	Commewijne	state	country:Suriname:3382998	1	495	2019-06-30 10:10:32.337699	\N	\N	100
state:Barisal:1337229	S2	Barisal	state	country:Bangladesh:1210997	1	2039	2019-07-01 13:04:35.803315	\N	\N	100
state:Kalmar:2702259	S2	Kalmar	state	country:Sweden:2661886	1	2361	2019-07-02 01:38:15.099273	\N	\N	100
state:Aluksne:461528	S2	Aluksne	state	region:Vidzeme:7639661	1	828	2019-06-30 10:10:35.126852	\N	\N	100
state:Airai:4037645	S2	Airai	state	country:Palau:1559581	1	492	2019-07-04 08:28:40.264962	\N	\N	100
state:Zasavska:3186905	S2	Zasavska	state	region:Zasavska:	1	512	2019-06-30 18:07:54.921806	\N	\N	100
state:Analamanga:7670856	S2	Analamanga	state	country:Madagascar:1062947	1	2583	2019-06-30 15:40:49.153805	\N	\N	100
state:Butel:833260	S2	Butel	state	region:GreaterSkopje:	1	1	2019-12-23 14:04:54.299998	\N	\N	100
state:KinshasaCity:2314300	S2	Kinshasa City	state	country:Congo:203312	1	311	2022-05-07 16:07:30.34517	\N	\N	100
state:Perugia:3171179	S2	Perugia	state	region:Umbria:3165048	1	3086	2019-06-30 17:58:41.680134	\N	\N	100
state:Isere:3012715	S2	Isère	state	region:RhoneAlpes:11071625	1	2576	2019-06-30 09:51:55.044164	\N	\N	100
state:Teruel:3108125	S2	Teruel	state	region:Aragon:3336899	1	2539	2019-06-30 21:06:22.89469	\N	\N	100
state:Bijeljina:3294896	S2	Bijeljina	state	region:RepuplikaSrpska:	1	3	2020-05-04 18:58:04.042266	\N	\N	100
country:N.Cyprus:	S2	N. Cyprus	country	continent:Asia:6255147	0	1043	2019-07-03 15:19:23.277192	\N	\N	100
region:SaintKitts:3575174	S2	Saint Kitts	region	country:SaintKittsAndNevis:3575174	0	516	2019-07-02 00:08:37.551599	\N	\N	100
state:Orhon:2055113	S2	Orhon	state	country:Mongolia:2029969	1	1012	2019-06-30 13:36:13.446216	\N	\N	100
state:LogoneOccidental:2429060	S2	Logone Occidental	state	country:Chad:2434508	1	1392	2019-06-30 18:18:15.160488	\N	\N	100
country:UnitedStatesMinorOutlyingIslands:5854968	S2	United States Minor Outlying Islands	country	continent:NorthAmerica:6255149	0	131	2022-06-18 00:01:26.715	\N	\N	100
state:Basilan:1726404	S2	Basilan	state	region:AutonomousRegionInMuslimMindanao(Armm):	1	1011	2019-06-30 14:10:51.9126	\N	\N	100
state:Dundagas:7628298	S2	Dundagas	state	region:Kurzeme:460496	1	1030	2019-06-30 18:18:49.322251	\N	\N	100
state:Ordubad:147365	S2	Ordubad	state	region:NaxcivanAutonomousRepublic:	1	3	2020-06-27 15:18:20.791962	\N	\N	100
state:Lhaviyani:1282096	S2	Lhaviyani	state	region:North:8030595	1	1009	2019-07-01 17:17:50.024645	\N	\N	100
state:Acquaviva:3345303	S2	Acquaviva	state	country:SanMarino:3345302	1	1017	2019-06-30 18:01:32.857697	\N	\N	100
state:Domagnano:3345305	S2	Domagnano	state	country:SanMarino:3345302	1	1017	2019-06-30 18:01:32.858327	\N	\N	100
state:Nimba:2274688	S2	Nimba	state	country:Liberia:2275384	1	1175	2019-06-30 22:17:59.568511	\N	\N	100
state:MirenKostanjevica:3239080	S2	Miren-Kostanjevica	state	region:Goriska:8988273	1	1	2020-10-22 15:05:59.760151	\N	\N	100
state:Probistip:863889	S2	Probištip	state	region:Northeastern:9072895	1	4	2019-12-23 14:51:55.650687	\N	\N	100
gulf:GulfOfOlenK	S2	Gulf of Olen‘k	gulf	root	1	3642	2019-06-30 13:47:09.614948	\N	\N	100
state:Guadeloupe:6544524	S2	Guadeloupe	state	region:Guadeloupe:6544524	1	1029	2019-07-02 00:17:05.907472	\N	\N	100
country:Guernsey:3042362	S2	Guernsey	country	continent:Europe:6255148	0	1015	2019-07-01 19:56:17.071249	\N	\N	100
state:Arezzo:3182882	S2	Arezzo	state	region:Toscana:3165361	1	1024	2019-06-30 18:01:32.865083	\N	\N	100
state:Cavally:2597333	S2	Cavally	state	country:IvoryCoast:2287781	1	2045	2019-06-30 22:25:52.787449	\N	\N	100
state:AltoParana:3439440	S2	Alto Paraná	state	country:Paraguay:3437598	1	4143	2019-06-30 22:35:52.158308	\N	\N	100
state:BinhPhuoc:1905480	S2	Bình Phước	state	region:DongNamBo:11497301	1	2510	2019-06-30 12:33:56.734557	\N	\N	100
state:Sikasso:2451184	S2	Sikasso	state	country:Mali:2453866	1	8745	2019-06-30 21:54:43.982671	\N	\N	100
state:EssequiboIslandsWestDemerara:3377274	S2	Essequibo Islands-West Demerara	state	country:Guyana:3378535	1	2584	2019-06-30 23:43:45.646105	\N	\N	100
state:PhraNakhonSiAyutthaya:1607530	S2	Phra Nakhon Si Ayutthaya	state	region:Central:10177180	1	491	2019-07-01 14:33:17.693159	\N	\N	100
state:Moka:934275	S2	Moka	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.757556	\N	\N	100
state:Cadiz:2520597	S2	Cádiz	state	region:Andalucia:2593109	1	2076	2019-07-01 19:39:40.000345	\N	\N	100
country:CostaRica:3624060	S2	Costa Rica	country	continent:NorthAmerica:6255149	0	7160	2019-07-01 03:03:22.028309	\N	\N	100
state:QuangTri:1568733	S2	Quảng Trị	state	region:BacTrungBo:11497251	1	509	2019-06-30 13:01:41.209812	\N	\N	100
strait:TsugaruStrait:2127627	S2	Tsugaru Strait	strait	root	1	1533	2019-06-30 10:14:38.651763	\N	\N	100
state:Sacatepequez:3590686	S2	Sacatepéquez	state	country:Guatemala:3595528	1	527	2019-06-30 10:16:17.534672	\N	\N	100
state:Koror:4037892	S2	Koror	state	country:Palau:1559581	1	492	2019-07-04 08:28:40.2654	\N	\N	100
state:Zeeland:2744011	S2	Zeeland	state	country:Netherlands:2750405	1	1768	2019-06-30 20:54:20.816907	\N	\N	100
state:Portuguesa:3629941	S2	Portuguesa	state	country:Venezuela:3625428	1	2716	2019-07-01 01:13:11.997694	\N	\N	100
state:SanMiguel:3583462	S2	San Miguel	state	country:ElSalvador:3585968	1	1048	2019-07-02 02:14:25.374866	\N	\N	100
state:BocasDelToro:3713954	S2	Bocas del Toro	state	country:Panama:3703430	1	1540	2019-07-01 03:10:33.230608	\N	\N	100
state:Cascade:241396	S2	Cascade	state	country:Seychelles:241170	1	487	2019-07-04 11:30:56.157662	\N	\N	100
state:Crnomelj:3202332	S2	Crnomelj	state	region:JugovzhodnaSlovenija:	1	8	2020-10-22 15:09:09.777694	\N	\N	100
state:Gitega:426271	S2	Gitega	state	country:Burundi:433561	1	1166	2019-07-02 15:50:07.689294	\N	\N	100
state:Caprivi:1090052	S2	Caprivi	state	country:Namibia:3355338	1	4258	2019-06-30 15:39:48.591976	\N	\N	100
state:Cebu:1717511	S2	Cebu	state	region:CentralVisayas(RegionVii):	1	619	2019-06-30 14:15:35.30527	\N	\N	100
state:Equateur:216661	S2	Équateur	state	country:Congo:2260494	1	2430	2022-05-07 16:03:53.295528	\N	\N	100
state:Takamaka:241151	S2	Takamaka	state	country:Seychelles:241170	1	485	2019-07-04 11:30:56.161085	\N	\N	100
state:Vipava:3239075	S2	Vipava	state	region:Goriska:8988273	1	1	2020-10-22 15:06:06.566648	\N	\N	100
state:Johor:1733049	S2	Johor	state	country:Malaysia:1733045	1	1531	2019-06-30 12:41:03.838847	\N	\N	100
state:EastBerbiceCorentyne:3378950	S2	East Berbice-Corentyne	state	country:Guyana:3378535	1	1486	2019-06-30 23:43:45.645596	\N	\N	100
region:Guadeloupe:6544524	S2	Guadeloupe	region	country:France:3017382	0	1029	2019-07-02 00:17:05.907914	\N	\N	100
state:AuCap:448410	S2	Au Cap	state	country:Seychelles:241170	1	484	2019-07-04 11:30:56.16155	\N	\N	100
country:SaintKittsAndNevis:3575174	S2	Saint Kitts and Nevis	country	continent:NorthAmerica:6255149	0	1033	2019-07-02 00:08:37.552055	\N	\N	100
state:Cibitoke:430020	S2	Cibitoke	state	country:Burundi:433561	1	1111	2019-07-02 15:49:13.402383	\N	\N	100
state:Chieti:3178795	S2	Chieti	state	region:Abruzzo:3183560	1	1035	2019-06-30 18:20:03.588484	\N	\N	100
state:Rivers:2324433	S2	Rivers	state	country:Nigeria:2328926	1	2047	2019-07-02 18:34:35.026352	\N	\N	100
state:Laguna:1708026	S2	Laguna	state	region:Calabarzon(RegionIvA):	1	37	2019-12-24 05:51:14.688417	\N	\N	100
region:Reunion:2972191	S2	Réunion	region	country:France:3017382	0	486	2019-07-01 14:21:59.412407	\N	\N	100
state:Creuse:3022516	S2	Creuse	state	region:Limousin:11071620	1	524	2019-06-30 21:27:09.145154	\N	\N	100
state:QuangNinh:1568758	S2	Quảng Ninh	state	region:DongBac:11497248	1	504	2019-06-30 13:02:01.260898	\N	\N	100
state:Brazzaville:2572183	S2	Brazzaville	state	country:Congo:203312	1	897	2019-06-30 18:44:11.273802	\N	\N	100
state:Sezana:3190944	S2	Sežana	state	region:ObalnoKraska:	1	7	2020-10-22 13:09:21.051689	\N	\N	100
state:ZamboangaDelNorte:1679429	S2	Zamboanga del Norte	state	region:ZamboangaPeninsula(RegionIx):	1	885	2019-06-30 14:10:10.033628	\N	\N	100
state:RoseAtoll:7309441	S2	Rose Atoll	state	country:AmericanSamoa:5880801	1	520	2022-06-17 23:24:46.266643	\N	\N	100
state:Masbate:1700711	S2	Masbate	state	region:Bicol(RegionV):	1	548	2019-06-30 14:10:11.504014	\N	\N	100
state:SanMarcos:3589801	S2	San Marcos	state	country:Guatemala:3595528	1	1020	2019-07-02 23:31:39.05433	\N	\N	100
state:Aklan:1731758	S2	Aklan	state	region:WesternVisayas(RegionVi):	1	9	2020-01-30 05:08:51.323051	\N	\N	100
state:EastFlanders:2789733	S2	East Flanders	state	region:Flemish:3337388	1	504	2019-06-30 21:09:19.727484	\N	\N	100
state:Canindeyu:3439216	S2	Canindeyú	state	country:Paraguay:3437598	1	2543	2019-06-30 22:35:38.956829	\N	\N	100
state:Gorontalo:1923046	S2	Gorontalo	state	country:Indonesia:1643084	1	2221	2019-06-30 13:48:19.672687	\N	\N	100
state:Donegal:2964751	S2	Donegal	state	region:Border:	1	2071	2019-06-30 18:41:17.557902	\N	\N	100
state:Landes:3007866	S2	Landes	state	region:Aquitaine:11071620	1	2082	2019-06-30 21:12:27.915996	\N	\N	100
state:Litija:3196424	S2	Litija	state	region:Osrednjeslovenska:	1	1838	2019-06-30 18:02:47.673543	\N	\N	100
state:Caltanissetta:2525447	S2	Caltanissetta	state	region:Sicily:2523119	1	8	2020-05-22 20:20:46.985183	\N	\N	100
state:Catanduanes:1717952	S2	Catanduanes	state	region:Bicol(RegionV):	1	512	2019-06-30 14:09:41.288267	\N	\N	100
region:Toscana:3165361	S2	Toscana	region	country:Italy:3175395	0	4142	2019-06-30 17:58:41.678326	\N	\N	100
state:KampongCham:1831172	S2	Kâmpóng Cham	state	country:Cambodia:1831722	1	991	2019-06-30 12:39:27.318827	\N	\N	100
state:Eastern:2301360	S2	Eastern	state	country:Ghana:2300660	1	3584	2019-07-02 00:19:35.108919	\N	\N	100
state:Gers:3016194	S2	Gers	state	region:MidiPyrenees:11071623	1	1043	2019-06-30 21:14:03.615907	\N	\N	100
state:Temotu:2178472	S2	Temotu	state	country:SolomonIslands:2103350	1	2034	2019-06-30 10:10:20.921669	\N	\N	100
state:Satakunta:831041	S2	Satakunta	state	country:Finland:660013	1	2794	2019-06-30 18:18:31.013081	\N	\N	100
state:BacLieu:1905675	S2	Bạc Liêu	state	region:DongBangSongCuuLong:1574717	1	492	2019-06-30 12:45:48.902285	\N	\N	100
country:Bermuda:3573345	S2	Bermuda	country	continent:NorthAmerica:6255149	0	281	2019-10-23 21:16:47.36878	\N	\N	100
state:Cienfuegos:3564120	S2	Cienfuegos	state	country:Cuba:3562981	1	531	2019-07-01 03:16:41.116383	\N	\N	100
region:Umbria:3165048	S2	Umbria	region	country:Italy:3175395	0	3096	2019-06-30 17:58:41.680707	\N	\N	100
state:SorTrondelag:3137400	S2	Sør-Trøndelag	state	country:Norway:3144096	1	6219	2019-06-30 21:06:48.470094	\N	\N	100
state:Westmeath:2960972	S2	Westmeath	state	region:Midlands:	1	682	2019-06-30 21:35:30.600564	\N	\N	100
state:Ayeyarwady:1321850	S2	Ayeyarwady	state	country:Myanmar:1327865	1	4938	2019-06-30 10:14:46.624336	\N	\N	100
state:Eure:3019317	S2	Eure	state	region:HauteNormandie:3013756	1	2036	2019-06-30 21:11:05.550007	\N	\N	100
region:NotranjskoKraska:	S2	Notranjsko-kraška	region	country:Slovenia:3190538	0	514	2019-06-30 18:02:41.806832	\N	\N	100
state:Harare:1105844	S2	Harare	state	country:Zimbabwe:878675	1	1030	2019-07-01 17:09:18.89063	\N	\N	100
sea:SeaOfMarmara:630669	S2	Sea of Marmara	sea	root	1	1719	2019-07-02 16:35:44.894661	\N	\N	100
state:Xekong:1904615	S2	Xékong	state	country:Laos:1655842	1	1045	2019-06-30 12:41:37.677456	\N	\N	100
state:AinTemouchent:2507899	S2	Aïn Témouchent	state	country:Algeria:2589581	1	1026	2019-06-30 22:53:18.981119	\N	\N	100
region:Pelagonia:	S2	Pelagonia	region	country:Macedonia:718075	0	951	2019-07-01 19:00:20.019959	\N	\N	100
state:ArgyllAndBute:6457407	S2	Argyll and Bute	state	region:HighlandsAndIslands:6621347	1	4060	2019-06-30 18:32:58.491226	\N	\N	100
state:PesaroEUrbino:3171172	S2	Pesaro e Urbino	state	region:Marche:3174004	1	1521	2019-06-30 18:01:32.863851	\N	\N	100
state:NorthEleuthera:8030553	S2	North Eleuthera	state	country:Bahamas:3572887	1	497	2019-07-02 23:50:23.639873	\N	\N	100
region:Ceuta:7577100	S2	Ceuta	region	country:Spain:2510769	0	1013	2019-07-01 19:36:34.229692	\N	\N	100
region:Liguria:3174725	S2	Liguria	region	country:Italy:3175395	0	1679	2019-07-02 01:50:07.345595	\N	\N	100
sound:KotzebueSound:5866727	S2	Kotzebue Sound	sound	root	1	3744	2019-06-30 10:14:50.862844	\N	\N	100
state:TuyenQuang:1559976	S2	Tuyên Quang	state	region:DongBac:11497248	1	208	2019-07-03 11:17:43.37218	\N	\N	100
state:Chin:1327132	S2	Chin	state	country:Myanmar:1327865	1	4653	2019-06-30 17:11:08.553931	\N	\N	100
state:Kapisa:1138255	S2	Kapisa	state	country:Afghanistan:1149361	1	965	2019-07-01 15:23:24.07481	\N	\N	100
state:Neuquen:3843122	S2	Neuquén	state	country:Argentina:3865483	1	13141	2019-07-01 01:35:43.152212	\N	\N	100
state:CrnaNaKoroskem:3239181	S2	Črna na Koroškem	state	region:Koroska:3217862	1	512	2019-06-30 18:07:54.922725	\N	\N	100
state:DakhletNouadhibou:2380426	S2	Dakhlet Nouadhibou	state	country:Mauritania:2378080	1	2579	2019-06-30 21:25:58.39589	\N	\N	100
state:Manabi:3654451	S2	Manabi	state	country:Ecuador:3658394	1	2409	2019-06-30 10:15:04.265883	\N	\N	100
month:03	S2	03	month	root	1	2312717	2019-09-27 13:58:23.296122	\N	\N	100
state:Mayabeque:7668827	S2	Mayabeque	state	country:Cuba:3562981	1	2080	2019-07-01 03:11:07.624303	\N	\N	100
state:SalaAdDin:91695	S2	Sala ad-Din	state	region:Iraq:99237	1	1816	2019-07-01 16:08:50.160747	\N	\N	100
state:Saramacca:3383110	S2	Saramacca	state	country:Suriname:3382998	1	985	2019-06-30 10:10:21.807781	\N	\N	100
state:Cher:3025480	S2	Cher	state	region:Centre:3027939	1	1549	2019-06-30 20:54:54.799883	\N	\N	100
state:HaaDhaalu:1282293	S2	Haa Dhaalu	state	region:UpperNorth:8030591	1	279	2021-06-10 08:54:39.687974	\N	\N	100
state:Caldas:3687951	S2	Caldas	state	country:Colombia:3686110	1	2064	2019-07-02 08:36:14.013837	\N	\N	100
state:PhnomPenh:1830103	S2	Phnom Penh	state	country:Cambodia:1831722	1	495	2019-06-30 12:43:14.971856	\N	\N	100
state:Thaa:1281881	S2	Thaa	state	region:UpperSouth:8030590	1	11	2021-06-20 08:53:11.976156	\N	\N	100
state:Mahanuvara:1241621	S2	Mahanuvara	state	region:MadhyamaPalata:	1	26	2020-04-20 09:47:35.457178	\N	\N	100
state:Varazdinska:3337528	S2	Varaždinska	state	country:Croatia:3202326	1	1797	2019-06-30 18:07:32.980414	\N	\N	100
state:Overijssel:2748838	S2	Overijssel	state	country:Netherlands:2750405	1	3133	2019-06-30 21:14:45.688728	\N	\N	100
state:Sevnica:3190949	S2	Sevnica	state	region:Spodnjeposavska:	1	771	2019-06-30 18:07:32.985308	\N	\N	100
state:Caazapa:3439296	S2	Caazapá	state	country:Paraguay:3437598	1	1428	2019-06-30 22:41:34.230872	\N	\N	100
state:CentarZupa:863841	S2	Centar župa	state	region:Southwestern:9072896	1	1	2020-08-14 23:45:35.737551	\N	\N	100
state:Debarca:7056271	S2	Debarca	state	region:Southwestern:9072896	1	2	2020-08-14 20:46:30.996298	\N	\N	100
state:Bexley:3333124	S2	Bexley	state	region:GreaterLondon:2648110	1	1	2020-02-26 14:11:02.991086	\N	\N	100
state:Ancash:3699674	S2	Áncash	state	country:Peru:3932488	1	4097	2019-07-04 00:24:56.360645	\N	\N	100
region:Nzerekore:8335091	S2	Nzérékoré	region	country:Guinea:2420477	0	3188	2019-06-30 22:17:20.287859	\N	\N	100
state:Zala:3042613	S2	Zala	state	region:WesternTransdanubia:	1	1031	2019-06-30 18:20:26.992711	\N	\N	100
state:Hilmand:1140043	S2	Hilmand	state	country:Afghanistan:1149361	1	7391	2019-07-02 14:01:10.794427	\N	\N	100
state:Mie:1857352	S2	Mie	state	region:Kinki:1859449	1	1026	2019-06-30 10:33:04.432708	\N	\N	100
state:Priekules:7628306	S2	Priekules	state	region:Kurzeme:460496	1	6	2020-05-15 09:00:01.191978	\N	\N	100
state:Melaka:1733035	S2	Melaka	state	country:Malaysia:1733045	1	1978	2019-06-30 12:43:48.976676	\N	\N	100
state:Ljubljana:3239318	S2	Ljubljana	state	region:Osrednjeslovenska:	1	510	2019-06-30 18:07:54.923621	\N	\N	100
state:SanJuan:3837152	S2	San Juan	state	country:Argentina:3865483	1	11483	2019-07-01 01:34:30.451247	\N	\N	100
state:Karlovacka:3337515	S2	Karlovacka	state	country:Croatia:3202326	1	1998	2019-06-30 18:02:47.677986	\N	\N	100
state:BosnianPodrinje:3343726	S2	Bosnian Podrinje	state	region:FederacijaBosnaIHercegovina:	1	512	2019-07-04 16:18:23.113688	\N	\N	100
state:Foca:3200837	S2	Foča	state	region:RepuplikaSrpska:	1	516	2019-07-04 16:18:23.114213	\N	\N	100
state:HaaAlifu:1282294	S2	Haa Alifu	state	region:UpperNorth:8030591	1	535	2021-06-10 08:55:37.11988	\N	\N	100
region:Campania:3181042	S2	Campania	region	country:Italy:3175395	0	1739	2019-06-30 18:17:52.001589	\N	\N	100
state:FlemishBrabant:3333250	S2	Flemish Brabant	state	region:Flemish:3337388	1	12	2020-02-27 21:04:02.380206	\N	\N	100
state:SwainSIsland:5881195	S2	Swain's Island	state	country:AmericanSamoa:5880801	1	136	2022-06-09 03:09:59.506427	\N	\N	100
lagoon:OzeroMogotoyevo:2123243	S2	Ozero Mogotoyevo	lagoon	root	1	2643	2019-06-30 11:54:17.861718	\N	\N	100
state:NewTaipeiCity:1665148	S2	New Taipei City	state	region:SpecialMunicipalities:	1	503	2019-06-30 11:03:24.522847	\N	\N	100
state:DarhanUul:2055111	S2	Darhan-Uul	state	country:Mongolia:2029969	1	1215	2019-06-30 13:35:19.18826	\N	\N	100
state:Yangon:1298822	S2	Yangon	state	country:Myanmar:1327865	1	1541	2019-06-30 10:14:47.173396	\N	\N	100
state:Ajlun:250799	S2	Ajlun	state	country:Jordan:248816	1	509	2019-06-30 19:16:04.29669	\N	\N	100
state:Ziri:3239087	S2	Žiri	state	region:Gorenjska:	1	510	2019-06-30 18:02:41.803667	\N	\N	100
state:Adamaoua:2236015	S2	Adamaoua	state	country:Cameroon:2233387	1	8577	2019-07-01 18:51:39.711022	\N	\N	100
state:Chimaltenango:3598571	S2	Chimaltenango	state	country:Guatemala:3595528	1	531	2019-06-30 10:16:17.540687	\N	\N	100
state:SouthLebanon:279894	S2	South Lebanon	state	country:Lebanon:272103	1	1018	2019-06-30 19:10:22.774539	\N	\N	100
region:Zasavska:	S2	Zasavska	region	country:Slovenia:3190538	0	1281	2019-06-30 18:07:32.982401	\N	\N	100
state:Lozere:2997288	S2	Lozère	state	region:LanguedocRoussillon:11071623	1	833	2019-06-30 21:13:49.266443	\N	\N	100
state:BinhDinh:1587871	S2	Bình Định	state	region:NamTrungBo:11497252	1	36	2020-07-16 12:16:52.85418	\N	\N	100
state:SouthAyrshire:3333235	S2	South Ayrshire	state	region:SouthWestern:2637295	1	1017	2019-06-30 18:33:38.539276	\N	\N	100
state:NegeriSembilan:1733043	S2	Negeri Sembilan	state	country:Malaysia:1733045	1	3064	2019-06-30 12:55:41.242918	\N	\N	100
state:PerthshireAndKinross:3333234	S2	Perthshire and Kinross	state	region:Eastern:2650458	1	1033	2019-06-30 18:33:53.196247	\N	\N	100
state:LaUnion:1707052	S2	La Union	state	region:Ilocos(RegionI):	1	1038	2019-06-30 11:03:43.789134	\N	\N	100
state:Dajabon:3508951	S2	Dajabón	state	country:DominicanRepublic:3508796	1	1016	2019-07-03 21:53:25.044466	\N	\N	100
region:Osrednjeslovenska:	S2	Osrednjeslovenska	region	country:Slovenia:3190538	0	1024	2019-06-30 18:02:41.804423	\N	\N	100
state:MonteCristi:3496200	S2	Monte Cristi	state	country:DominicanRepublic:3508796	1	2031	2019-07-03 21:45:04.520695	\N	\N	100
state:PhuTho:1905577	S2	Phú Thọ	state	region:DongBac:11497248	1	11	2021-11-05 16:06:55.666826	\N	\N	100
state:SisackoMoslavacka:3337526	S2	Sisacko-Moslavacka	state	country:Croatia:3202326	1	1490	2019-06-30 18:02:47.676799	\N	\N	100
state:Labe:8335089	S2	Labé	state	region:Labe:8335089	1	8	2020-05-16 20:10:46.615115	\N	\N	100
country:CyprusU.N.BufferZone:4036776	S2	Cyprus U.N. Buffer Zone	country	continent:Asia:6255147	0	1559	2019-07-03 15:19:23.276428	\N	\N	100
state:Guatemala:3595530	S2	Guatemala	state	country:Guatemala:3595528	1	531	2019-06-30 10:16:17.541976	\N	\N	100
state:Penghu:1670651	S2	Penghu	state	region:TaiwanProvince:1668284	1	988	2019-06-30 11:03:13.181013	\N	\N	100
state:Toyama:1849872	S2	Toyama	state	region:Chubu:1864496	1	512	2019-06-30 10:33:52.153914	\N	\N	100
state:WesternHighlands:2083551	S2	Western Highlands	state	country:PapuaNewGuinea:2088628	1	1012	2019-06-30 20:06:38.7941	\N	\N	100
state:IvancnaGorica:3239191	S2	Ivancna Gorica	state	region:Osrednjeslovenska:	1	510	2019-06-30 18:02:41.804051	\N	\N	100
country:NewCaledonia:2139685	S2	New Caledonia	country	continent:Oceania:6255151	0	2663	2019-07-02 08:06:25.65648	\N	\N	100
state:Macenta:2417987	S2	Macenta	state	region:Nzerekore:8335091	1	1004	2019-06-30 22:17:20.287105	\N	\N	100
state:Manatuto:1636525	S2	Manatuto	state	country:TimorLeste:1966436	1	506	2019-06-30 10:33:37.756236	\N	\N	100
state:Kep:1830937	S2	Kep	state	country:Cambodia:1831722	1	497	2019-06-30 12:45:30.123577	\N	\N	100
state:Miaoli:1671968	S2	Miaoli	state	region:TaiwanProvince:1668284	1	1003	2019-06-30 11:04:11.794058	\N	\N	100
state:Imisli:147983	S2	İmişli	state	region:AranEconomicRegion:	1	989	2019-07-03 16:39:25.89594	\N	\N	100
state:Sokoto:2322907	S2	Sokoto	state	country:Nigeria:2328926	1	4160	2019-06-30 17:57:21.895228	\N	\N	100
country:Malta:2562770	S2	Malta	country	continent:Europe:6255148	0	1028	2019-07-02 17:58:59.514731	\N	\N	100
country:AshmoreAndCartierIslands:2077507	S2	Ashmore and Cartier Islands	country	continent:Oceania:6255151	0	494	2019-07-03 08:27:38.000219	\N	\N	100
state:KampongChhnang:1831166	S2	Kâmpóng Chhnang	state	country:Cambodia:1831722	1	1007	2019-06-30 12:46:18.933887	\N	\N	100
state:Aichi:1865694	S2	Aichi	state	region:Chubu:1864496	1	518	2019-06-30 10:33:28.871684	\N	\N	100
region:Lazio:3174976	S2	Lazio	region	country:Italy:3175395	0	5133	2019-06-30 17:58:41.679463	\N	\N	100
state:JohnstonAtoll:5854929	S2	Johnston Atoll	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	106	2022-06-10 01:15:32.657769	\N	\N	100
state:NorthAyshire:3333232	S2	North Ayshire	state	region:SouthWestern:2637295	1	10	2020-07-19 17:51:00.200382	\N	\N	100
bay:ChesapeakeBay:4351177	S2	Chesapeake Bay	bay	root	1	1195	2019-07-01 02:15:44.381198	\N	\N	100
state:Taoyuan:1667900	S2	Taoyuan	state	region:TaiwanProvince:1668284	1	10	2020-03-01 06:05:14.490599	\N	\N	100
day:28	S2	28	day	root	1	941311	2019-06-30 14:12:50.067516	\N	\N	100
state:Puttalama:1229292	S2	Puttalama	state	region:VayambaPalata:	1	1032	2019-06-30 15:35:57.954628	\N	\N	100
country:Baikonur:1521368	S2	Baikonur	country	continent:Asia:6255147	0	3050	2019-06-30 14:43:30.797539	\N	\N	100
state:Savona:3167021	S2	Savona	state	region:Liguria:3174725	1	505	2019-07-02 01:50:07.345055	\N	\N	100
state:NorthEast:7535955	S2	North East	state	country:Singapore:1880251	1	501	2019-06-30 12:41:03.835346	\N	\N	100
state:SaintPhilip:3373553	S2	Saint Philip	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.473297	\N	\N	100
region:Gorenjska:	S2	Gorenjska	region	country:Slovenia:3190538	0	1016	2019-06-30 18:07:54.924928	\N	\N	100
bay:HamiltonInlet:5969825	S2	Hamilton Inlet	bay	root	1	3904	2019-06-30 23:42:02.761195	\N	\N	100
region:PaisVasco:3336903	S2	País Vasco	region	country:Spain:2510769	0	2029	2019-07-01 19:32:25.830141	\N	\N	100
state:BayanOlgiy:1516278	S2	Bayan-Ölgiy	state	country:Mongolia:2029969	1	10403	2019-06-30 15:27:57.792457	\N	\N	100
state:Idrija:3199169	S2	Idrija	state	region:Goriska:8988273	1	511	2019-06-30 18:02:41.804843	\N	\N	100
state:Sava:7670846	S2	Sava	state	country:Madagascar:1062947	1	2682	2019-06-30 15:34:59.88629	\N	\N	100
state:Mizoram:1262963	S2	Mizoram	state	region:Northeast:10575550	1	2031	2019-07-03 13:06:29.545926	\N	\N	100
state:LArtibonite:3731053	S2	L'Artibonite	state	country:Haiti:3723988	1	1025	2019-07-02 08:25:08.513194	\N	\N	100
state:Kanagawa:1860291	S2	Kanagawa	state	region:Kanto:1860102	1	11	2020-09-09 06:30:35.049591	\N	\N	100
state:Ancona:3183087	S2	Ancona	state	region:Marche:3174004	1	510	2019-06-30 18:21:34.771629	\N	\N	100
state:CamarinesSur:1719845	S2	Camarines Sur	state	region:Bicol(RegionV):	1	192	2019-06-30 14:11:33.802505	\N	\N	100
state:AnseRoyale:241444	S2	Anse Royale	state	country:Seychelles:241170	1	485	2019-07-04 11:30:56.160251	\N	\N	100
state:Waterford:2960991	S2	Waterford	state	region:SouthEast:	1	2039	2019-06-30 21:37:07.721102	\N	\N	100
state:Osaka:1853904	S2	Ōsaka	state	region:Kinki:1859449	1	4	2021-11-24 04:50:28.807865	\N	\N	100
state:Zamfara:2595349	S2	Zamfara	state	country:Nigeria:2328926	1	6737	2019-06-30 17:57:21.894787	\N	\N	100
state:Cork:2965139	S2	Cork	state	region:SouthWest:	1	1037	2019-06-30 21:37:07.7217	\N	\N	100
region:NamTrungBo:11497252	S2	Nam Trung Bộ	region	country:Vietnam:1562822	0	5318	2019-07-02 12:15:19.969006	\N	\N	100
state:Pomeranian:3337496	S2	Pomeranian	state	country:Poland:798544	1	5628	2019-06-30 18:01:06.692565	\N	\N	100
bay:CordovaBay:5927815	S2	Cordova Bay	bay	root	1	1478	2019-07-01 08:49:28.599533	\N	\N	100
state:Huambo:3348310	S2	Huambo	state	country:Angola:3351879	1	5639	2019-07-02 18:02:07.225229	\N	\N	100
state:BelOmbre:241424	S2	Bel Ombre	state	country:Seychelles:241170	1	964	2019-07-02 14:48:34.909833	\N	\N	100
state:OuedElDahab:	S2	Oued el Dahab	state	country:Morocco:2542007	1	7109	2019-06-30 21:25:34.364714	\N	\N	100
state:Androy:7670911	S2	Androy	state	country:Madagascar:1062947	1	2757	2019-06-30 15:15:13.695868	\N	\N	100
state:Kaegalla:1240722	S2	Kægalla	state	region:SabaragamuvaPalata:	1	517	2019-06-30 15:37:28.445302	\N	\N	100
state:Mannarama:1236148	S2	Mannārama	state	region:UturuPalata:	1	521	2019-06-30 15:53:41.204597	\N	\N	100
state:Gampaha:1246005	S2	Gampaha	state	region:BasnahiraPalata:	1	1021	2019-06-30 15:35:57.954106	\N	\N	100
state:TreintaYTres:3439780	S2	Treinta y Tres	state	country:Uruguay:3439705	1	2055	2019-06-30 22:37:34.012865	\N	\N	100
state:Tanga:149595	S2	Tanga	state	country:Tanzania:149590	1	3611	2019-06-30 20:22:46.268036	\N	\N	100
state:ChiangMai:1153670	S2	Chiang Mai	state	region:Northern:6695803	1	2644	2019-06-30 10:14:46.671294	\N	\N	100
state:Madang:2091993	S2	Madang	state	country:PapuaNewGuinea:2088628	1	3523	2019-06-30 20:07:07.05307	\N	\N	100
state:Tolmin:3189037	S2	Tolmin	state	region:Goriska:8988273	1	1010	2019-06-30 18:07:54.925373	\N	\N	100
state:Utrecht:2745909	S2	Utrecht	state	country:Netherlands:2750405	1	1015	2019-06-30 21:14:17.021834	\N	\N	100
state:Mulativ:1234392	S2	Mulativ	state	region:UturuPalata:	1	513	2019-06-30 15:58:40.683096	\N	\N	100
state:Koper:3197752	S2	Koper	state	region:ObalnoKraska:	1	1016	2019-06-30 18:02:41.805616	\N	\N	100
state:IlirskaBistrica:3199130	S2	Ilirska Bistrica	state	region:NotranjskoKraska:	1	512	2019-06-30 18:02:41.806427	\N	\N	100
state:Kocevje:3197942	S2	Kocevje	state	region:JugovzhodnaSlovenija:	1	1030	2019-06-30 18:02:41.807228	\N	\N	100
state:PrimorskoGoranska:3337524	S2	Primorsko-Goranska	state	country:Croatia:3202326	1	1539	2019-06-30 18:02:41.802862	\N	\N	100
region:Dublin:2964573	S2	Dublin	region	country:Ireland:2963597	0	1017	2019-06-30 21:35:30.60002	\N	\N	100
state:Yardimli:146962	S2	Yardımlı	state	region:LankaranEconomicRegion:	1	13	2020-04-28 16:58:54.924253	\N	\N	100
state:Tandjile:2425287	S2	Tandjilé	state	country:Chad:2434508	1	1568	2019-06-30 18:09:46.289252	\N	\N	100
region:GreaterLondon:2648110	S2	Greater London	region	country:UnitedKingdom:2635167	0	2025	2019-07-01 19:37:33.855671	\N	\N	100
state:DixHuitMontagnes:2597323	S2	Dix-Huit Montagnes	state	country:IvoryCoast:2287781	1	2206	2019-06-30 22:27:03.342256	\N	\N	100
state:Olomoucky:3339542	S2	Olomoucký	state	country:CzechRepublic:3077311	1	2055	2019-06-30 18:17:37.107488	\N	\N	100
gulf:KaraginskiyGulf	S2	Karaginskiy Gulf	gulf	root	1	8061	2019-07-01 09:34:44.15032	\N	\N	100
month:07	S2	07	month	root	1	2998631	2019-07-01 08:50:48.081186	\N	\N	100
state:Maryland:4361885	S2	Maryland	state	region:South:11887752	1	3196	2019-07-01 02:08:32.095137	\N	\N	100
state:Ntungamo:227592	S2	Ntungamo	state	region:Western:8260675	1	7	2020-04-27 14:06:48.761049	\N	\N	100
month:11	S2	11	month	root	1	1940913	2019-11-01 02:23:44.92083	\N	\N	100
state:Khartoum:379253	S2	Khartoum	state	region:Khartum:379253	1	2376	2019-06-30 16:09:31.717078	\N	\N	100
state:Duzce:865521	S2	Düzce	state	country:Turkey:298795	1	1023	2019-07-01 22:20:44.996204	\N	\N	100
state:Siena:3166546	S2	Siena	state	region:Toscana:3165361	1	1026	2019-06-30 17:58:41.677729	\N	\N	100
state:SouthEast:7535956	S2	South East	state	country:Singapore:1880251	1	501	2019-06-30 12:41:03.835958	\N	\N	100
state:Gandaki:1283410	S2	Gandaki	state	region:West:1283118	1	530	2019-06-30 15:45:03.142165	\N	\N	100
region:MaltaMajjistral:	S2	Malta Majjistral	region	country:Malta:2562770	0	1027	2019-07-02 17:58:59.51413	\N	\N	100
state:F.C.T.:1162015	S2	F.C.T.	state	country:Pakistan:1168579	1	503	2019-06-30 16:19:10.114419	\N	\N	100
state:Viterbo:3164038	S2	Viterbo	state	region:Lazio:3174976	1	2032	2019-06-30 17:58:41.678898	\N	\N	100
state:TheSnares:2182610	S2	The Snares	state	region:NewZealandOutlyingIslands:	1	264	2022-06-16 03:14:50.920416	\N	\N	100
state:Yevrey:2026639	S2	Yevrey	state	region:FarEastern:11961349	1	4652	2019-06-30 11:39:00.277286	\N	\N	100
region:SouthWest:	S2	South-West	region	country:Ireland:2963597	0	2077	2019-06-30 21:37:07.722205	\N	\N	100
region:ZamboangaPeninsula(RegionIx):	S2	Zamboanga Peninsula (Region IX)	region	country:Philippines:1694008	0	1409	2019-06-30 14:10:10.0341	\N	\N	100
state:ElSeybo:3506189	S2	El Seybo	state	country:DominicanRepublic:3508796	1	1550	2019-06-30 23:44:43.899837	\N	\N	100
state:Oyo:2325190	S2	Oyo	state	country:Nigeria:2328926	1	2816	2019-06-30 17:57:50.556986	\N	\N	100
state:AlBatnahSouth:411736	S2	Al Batnah South	state	country:Oman:286963	1	1014	2019-06-30 15:48:58.678985	\N	\N	100
state:AlmatyCity:1537162	S2	Almaty City	state	country:Kazakhstan:1522867	1	998	2019-06-30 16:42:14.365249	\N	\N	100
region:FujianProvince:	S2	Fujian Province	region	country:Taiwan:1668284	0	494	2019-07-03 10:28:21.15727	\N	\N	100
state:Segou:2451477	S2	Ségou	state	country:Mali:2453866	1	10113	2019-06-30 21:54:38.859856	\N	\N	100
state:SentjurPriCelju:3191028	S2	Šentjur pri Celju	state	region:Savinjska:3186550	1	769	2019-06-30 18:07:32.984468	\N	\N	100
state:Boulkiemde:2361988	S2	Boulkiemdé	state	region:CentreOuest:6930707	1	520	2019-06-30 09:51:49.262981	\N	\N	100
state:Imbabura:3655635	S2	Imbabura	state	country:Ecuador:3658394	1	662	2019-07-02 08:22:41.243698	\N	\N	100
state:Montegiardino:3345308	S2	Montegiardino	state	country:SanMarino:3345302	1	1015	2019-06-30 18:01:32.855182	\N	\N	100
state:Faetano:3345306	S2	Faetano	state	country:SanMarino:3345302	1	1017	2019-06-30 18:01:32.855838	\N	\N	100
state:ArchipelDesKerguelen:1546558	S2	Archipel des Kerguelen	state	country:FrenchSouthernAndAntarcticLands:1546748	1	2037	2019-07-01 13:06:21.799105	\N	\N	100
state:Volta:2294234	S2	Volta	state	country:Ghana:2300660	1	5614	2019-07-02 00:30:17.822765	\N	\N	100
region:Bicol(RegionV):	S2	Bicol (Region V)	region	country:Philippines:1694008	0	1778	2019-06-30 14:09:28.160437	\N	\N	100
state:SanCristobal:3493186	S2	San Cristóbal	state	country:DominicanRepublic:3508796	1	1024	2019-06-30 23:42:24.684021	\N	\N	100
state:Mzimba:925496	S2	Mzimba	state	region:Northern:924591	1	2086	2019-07-01 17:15:11.028295	\N	\N	100
state:Littoral:2597275	S2	Littoral	state	country:Benin:2395170	1	1008	2019-06-30 18:00:20.972801	\N	\N	100
country:NorfolkIsland:2155115	S2	Norfolk Island	country	continent:Oceania:6255151	0	522	2022-06-07 02:54:43.180616	\N	\N	100
state:Jihomoravsky:3339536	S2	Jihomoravský	state	country:CzechRepublic:3077311	1	2074	2019-06-30 18:16:29.744601	\N	\N	100
state:Plateau:2597277	S2	Plateau	state	country:Benin:2395170	1	2020	2019-06-30 18:00:20.974096	\N	\N	100
state:Lagos:2332453	S2	Lagos	state	country:Nigeria:2328926	1	1023	2019-06-30 18:02:08.875478	\N	\N	100
state:Mila:2487449	S2	Mila	state	country:Algeria:2589581	1	1011	2019-07-02 01:45:50.16624	\N	\N	100
strait:BabElMandeb:54365	S2	Bab el Mandeb	strait	root	1	1548	2019-06-30 20:34:11.002607	\N	\N	100
region:MidWest:	S2	Mid-West	region	country:Ireland:2963597	0	1666	2019-06-30 21:42:09.473207	\N	\N	100
region:Goriska:8988273	S2	Goriška	region	country:Slovenia:3190538	0	4040	2019-06-30 18:02:41.805225	\N	\N	100
state:TasmanDistrict:2181818	S2	Tasman District	state	region:SouthIsland:2182504	1	514	2019-07-04 11:59:57.207698	\N	\N	100
state:Tokelau:4031074	S2	Tokelau	state	country:NewZealand:2186224	1	264	2022-06-09 03:11:11.698474	\N	\N	100
state:Limon:3623064	S2	Limón	state	country:CostaRica:3624060	1	1529	2019-07-01 03:08:59.131417	\N	\N	100
state:Cəbrayil:148141	S2	Cəbrayıl	state	region:YukhariGarabakhEconomicRegion:	1	2	2020-04-28 16:59:30.519206	\N	\N	100
state:Opole:3337495	S2	Opole	state	country:Poland:798544	1	5663	2019-06-30 17:58:15.391899	\N	\N	100
state:DytikiMakedonia:6697811	S2	Dytiki Makedonia	state	country:Greece:390903	1	2723	2019-07-01 18:53:22.893427	\N	\N	100
state:NorthWest:7535958	S2	North West	state	country:Singapore:1880251	1	501	2019-06-30 12:41:03.836552	\N	\N	100
state:Zəngilan:146900	S2	Zəngilan	state	region:KalbajarLachinEconomicRegion:	1	2	2020-04-28 16:59:30.52078	\N	\N	100
state:Pool:2255404	S2	Pool	state	country:Congo:2260494	1	2447	2019-06-30 18:35:55.901978	\N	\N	100
state:Matera:3173718	S2	Matera	state	region:Basilicata:3182306	1	2071	2019-07-02 17:50:02.889128	\N	\N	100
state:Irbid:248944	S2	Irbid	state	country:Jordan:248816	1	2032	2019-06-30 19:05:32.929913	\N	\N	100
state:Ica:3938526	S2	Ica	state	country:Peru:3932488	1	3062	2019-07-01 02:16:39.633572	\N	\N	100
state:Sousse:2464912	S2	Sousse	state	country:Tunisia:2464461	1	1016	2019-06-30 18:03:23.587715	\N	\N	100
state:Itasy:7670855	S2	Itasy	state	country:Madagascar:1062947	1	3238	2019-06-30 15:40:49.153297	\N	\N	100
state:Qazax:586087	S2	Qazax	state	region:GanjaGazakhEconomicRegion:	1	493	2019-07-01 16:10:46.964714	\N	\N	100
day:22	S2	22	day	root	1	941821	2019-07-22 03:17:22.126815	\N	\N	100
state:GrandBassa:2276630	S2	Grand Bassa	state	country:Liberia:2275384	1	1998	2019-06-30 21:57:30.29419	\N	\N	100
state:FinlandProper:830708	S2	Finland Proper	state	country:Finland:660013	1	2479	2019-06-30 18:00:31.57137	\N	\N	100
state:Roma:3169069	S2	Roma	state	region:Lazio:3174976	1	1539	2019-06-30 18:03:56.578157	\N	\N	100
state:Medimurska:3337521	S2	Medimurska	state	country:Croatia:3202326	1	1792	2019-06-30 18:07:32.979966	\N	\N	100
state:NordKivu:206938	S2	Nord-Kivu	state	country:Congo:203312	1	6690	2019-06-30 16:07:13.371585	\N	\N	100
state:Bizerte:2472699	S2	Bizerte	state	country:Tunisia:2464461	1	1523	2019-06-30 18:17:13.664414	\N	\N	100
state:Canelones:3443411	S2	Canelones	state	country:Uruguay:3439705	1	1025	2019-06-30 22:42:21.055662	\N	\N	100
state:NorthAndros:8030552	S2	North Andros	state	country:Bahamas:3572887	1	1498	2019-07-02 23:55:35.938989	\N	\N	100
state:Mahdia:2473574	S2	Mahdia	state	country:Tunisia:2464461	1	1021	2019-06-30 18:04:04.163568	\N	\N	100
state:ThuaThienHue:1565033	S2	Thừa Thiên - Huế	state	region:BacTrungBo:11497251	1	1905	2019-06-30 12:37:45.933281	\N	\N	100
state:WestBosnia:3343740	S2	West Bosnia	state	region:FederacijaBosnaIHercegovina:	1	1039	2019-07-02 17:55:10.548514	\N	\N	100
region:NorthernIreland:2635167	S2	Northern Ireland	region	country:UnitedKingdom:2635167	0	4122	2019-06-30 18:34:03.681867	\N	\N	100
state:WestBengal:1252881	S2	West Bengal	state	region:East:1272123	1	8339	2019-07-01 13:07:15.865144	\N	\N	100
state:Antique:1730491	S2	Antique	state	region:WesternVisayas(RegionVi):	1	1084	2019-06-30 14:13:52.977301	\N	\N	100
state:Teramo:3165802	S2	Teramo	state	region:Abruzzo:3183560	1	1031	2019-06-30 18:05:00.958397	\N	\N	100
state:Saare:588879	S2	Saare	state	country:Estonia:453733	1	2167	2019-06-30 18:06:27.677057	\N	\N	100
state:Ards:11353074	S2	Ards	state	region:NorthernIreland:2635167	1	3	2020-04-25 16:39:04.021871	\N	\N	100
state:Zlinsky:3339578	S2	Zlínský	state	country:CzechRepublic:3077311	1	2070	2019-06-30 18:03:10.560357	\N	\N	100
state:Pisa:3170646	S2	Pisa	state	region:Toscana:3165361	1	16	2020-04-28 17:43:19.105705	\N	\N	100
country:CaymanIslands:3580718	S2	Cayman Islands	country	continent:NorthAmerica:6255149	0	1023	2019-07-01 03:03:52.870661	\N	\N	100
state:Wexford:2960963	S2	Wexford	state	region:SouthEast:	1	1018	2019-06-30 21:42:11.038421	\N	\N	100
state:Osun:2597365	S2	Osun	state	country:Nigeria:2328926	1	1068	2019-06-30 18:00:11.984496	\N	\N	100
state:Aizputes:7628305	S2	Aizputes	state	region:Kurzeme:460496	1	510	2019-06-30 18:17:43.755073	\N	\N	100
region:Central:	S2	Central	region	country:Bhutan:1252634	0	2502	2019-07-04 10:20:40.550149	\N	\N	100
state:Trbovlje:3188914	S2	Trbovlje	state	region:Zasavska:	1	272	2019-06-30 18:07:32.981981	\N	\N	100
state:Tak:1150489	S2	Tak	state	region:Western:6470693	1	2639	2019-06-30 10:14:46.669463	\N	\N	100
state:SlovenjGradec:3190535	S2	Slovenj Gradec	state	region:Koroska:3217862	1	250	2019-06-30 18:07:32.982811	\N	\N	100
state:Prague:3067695	S2	Prague	state	country:CzechRepublic:3077311	1	1010	2019-06-30 18:16:29.611315	\N	\N	100
state:Kebbi:2597363	S2	Kebbi	state	country:Nigeria:2328926	1	4696	2019-06-30 17:59:35.716584	\N	\N	100
state:Zamboanga:1679429	S2	Zamboanga	state	region:ZamboangaPeninsula(RegionIx):	1	1026	2019-06-30 14:10:51.912117	\N	\N	100
state:Cavan:2965534	S2	Cavan	state	region:Border:	1	1026	2019-06-30 18:32:19.099098	\N	\N	100
state:Latina:3175057	S2	Latina	state	region:Lazio:3174976	1	522	2019-06-30 18:19:20.216562	\N	\N	100
state:Siemreab:	S2	Siemréab	state	country:Cambodia:1831722	1	2009	2019-07-03 11:10:55.383283	\N	\N	100
state:Kabul:1138957	S2	Kabul	state	country:Afghanistan:1149361	1	1454	2019-07-01 15:23:24.075925	\N	\N	100
state:SumateraBarat:1626197	S2	Sumatera Barat	state	country:Indonesia:1643084	1	3952	2019-06-30 11:54:25.972448	\N	\N	100
state:NanaGrebizi:2386243	S2	Nana-Grébizi	state	country:CentralAfricanRepublic:239880	1	2058	2019-07-02 16:10:56.189571	\N	\N	100
state:KranjskaGora:3239105	S2	Kranjska Gora	state	region:Gorenjska:	1	1016	2019-06-30 18:22:15.614538	\N	\N	100
state:Pichincha:3653224	S2	Pichincha	state	country:Ecuador:3658394	1	664	2019-07-02 08:23:29.746932	\N	\N	100
state:Mbeya:154375	S2	Mbeya	state	country:Tanzania:149590	1	5966	2019-07-01 16:40:07.914786	\N	\N	100
region:Border:	S2	Border	region	country:Ireland:2963597	0	2735	2019-06-30 18:32:19.099627	\N	\N	100
state:Meta:3674810	S2	Meta	state	country:Colombia:3686110	1	10388	2019-07-01 01:13:25.152814	\N	\N	100
state:Down:2651037	S2	Down	state	region:NorthernIreland:2635167	1	1014	2019-06-30 18:34:03.681411	\N	\N	100
state:Grosseto:3175784	S2	Grosseto	state	region:Toscana:3165361	1	1035	2019-06-30 18:20:14.426705	\N	\N	100
state:Triesen:3042036	S2	Triesen	state	country:Liechtenstein:3042058	1	493	2019-07-02 01:26:58.303912	\N	\N	100
state:Venezia:3164600	S2	Venezia	state	region:Veneto:3164604	1	1524	2019-06-30 18:19:31.881018	\N	\N	100
region:VayambaPalata:	S2	Vayamba paḷāta	region	country:SriLanka:1227603	0	1057	2019-06-30 15:35:57.955106	\N	\N	100
state:Brokopondo:3384481	S2	Brokopondo	state	country:Suriname:3382998	1	992	2019-06-30 10:10:27.952054	\N	\N	100
state:Clare:2965479	S2	Clare	state	region:MidWest:	1	1018	2019-06-30 21:43:05.320677	\N	\N	100
state:Bovec:3239100	S2	Bovec	state	region:Goriska:8988273	1	1012	2019-06-30 18:22:15.615179	\N	\N	100
state:Gabes:2468365	S2	Gabès	state	country:Tunisia:2464461	1	967	2019-06-30 18:08:46.307264	\N	\N	100
state:Prilep:863888	S2	Prilep	state	region:Pelagonia:	1	942	2019-07-01 19:00:20.019573	\N	\N	100
state:Vysocina:3339538	S2	Vysočina	state	country:CzechRepublic:3077311	1	1535	2019-06-30 18:16:29.745303	\N	\N	100
state:Nicas:7628301	S2	Nicas	state	region:Kurzeme:460496	1	1026	2019-06-30 18:18:34.773615	\N	\N	100
state:Rieti:3169411	S2	Rieti	state	region:Lazio:3174976	1	518	2019-06-30 18:08:29.35336	\N	\N	100
state:MoyenOgooue:2397842	S2	Moyen-Ogooué	state	country:Gabon:2400553	1	2562	2019-07-01 18:47:10.483253	\N	\N	100
state:Iringa:159067	S2	Iringa	state	country:Tanzania:149590	1	4575	2019-07-01 17:08:56.787674	\N	\N	100
state:KienGiang:1579008	S2	Kiên Giang	state	region:DongBangSongCuuLong:1574717	1	511	2019-06-30 12:45:30.121764	\N	\N	100
state:EileanSiar:2634428	S2	Eilean Siar	state	region:HighlandsAndIslands:6621347	1	2020	2019-06-30 18:39:33.343585	\N	\N	100
state:Romblon:1691537	S2	Romblon	state	region:Mimaropa(RegionIvB):	1	1086	2019-06-30 14:08:07.285404	\N	\N	100
state:Trapani:2522875	S2	Trapani	state	region:Sicily:2523119	1	2451	2019-06-30 18:03:16.28514	\N	\N	100
state:Ventspils:454310	S2	Ventspils	state	region:Kurzeme:460496	1	1026	2019-06-30 18:19:09.943008	\N	\N	100
state:Correze:3023532	S2	Corrèze	state	region:Limousin:11071620	1	517	2019-06-30 21:17:49.850284	\N	\N	100
country:Aland:661882	S2	Åland	country	continent:Europe:6255148	0	937	2019-06-30 18:20:05.502274	\N	\N	100
state:Palermo:2523918	S2	Palermo	state	region:Sicily:2523119	1	1048	2019-06-30 18:20:26.989268	\N	\N	100
state:Lendava:3344935	S2	Lendava	state	region:Pomurska:	1	1026	2019-06-30 18:20:26.990867	\N	\N	100
state:Vas:3043047	S2	Vas	state	region:WesternTransdanubia:	1	518	2019-06-30 18:19:06.360583	\N	\N	100
state:CentralAndros:8030543	S2	Central Andros	state	country:Bahamas:3572887	1	1504	2019-07-02 23:55:35.939508	\N	\N	100
state:Udine:3165071	S2	Udine	state	region:FriuliVeneziaGiulia:3176525	1	2025	2019-06-30 18:19:31.881557	\N	\N	100
region:Limousin:11071620	S2	Limousin	region	country:France:3017382	0	2619	2019-06-30 21:05:12.574513	\N	\N	100
state:Burgenland:2781194	S2	Burgenland	state	country:Austria:2782113	1	2746	2019-06-30 18:01:58.492036	\N	\N	100
state:NovaGoriska:3194451	S2	Nova Goriška	state	region:Goriska:8988273	1	1513	2019-06-30 18:19:31.879257	\N	\N	100
state:Rimini:6457404	S2	Rimini	state	region:EmiliaRomagna:3177401	1	1016	2019-06-30 18:21:34.770934	\N	\N	100
state:Isernia:3175444	S2	Isernia	state	region:Molise:3173222	1	1024	2019-06-30 18:17:52.00074	\N	\N	100
state:Magway:1312604	S2	Magway	state	country:Myanmar:1327865	1	5160	2019-06-30 16:59:21.882959	\N	\N	100
state:Karnali:1283245	S2	Karnali	state	region:MidWestern:7289706	1	3012	2019-06-30 15:43:56.81179	\N	\N	100
state:Derry:2643736	S2	Derry	state	region:NorthernIreland:2635167	1	1031	2019-06-30 18:41:46.301549	\N	\N	100
state:Rakahanga:4035557	S2	Rakahanga	state	country:CookIslands:1899402	1	135	2022-06-07 02:57:01.674554	\N	\N	100
state:ShetlandIslands:2638010	S2	Shetland Islands	state	region:HighlandsAndIslands:6621347	1	3139	2019-06-30 18:32:53.133413	\N	\N	100
state:MidwayIslands:5854943	S2	Midway Islands	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	134	2022-06-07 02:55:15.890813	\N	\N	100
state:Kavadartsi:863861	S2	Kavadartsi	state	region:Vardar:833492	1	945	2019-07-01 19:00:20.018758	\N	\N	100
state:Moyle:2642068	S2	Moyle	state	region:NorthernIreland:2635167	1	2027	2019-06-30 18:33:38.538771	\N	\N	100
state:Parwan:6957555	S2	Parwan	state	country:Afghanistan:1149361	1	959	2019-07-01 15:23:24.074051	\N	\N	100
state:Hillingdon:3333154	S2	Hillingdon	state	region:GreaterLondon:2648110	1	1012	2019-07-01 19:41:29.386519	\N	\N	100
country:Israel:294640	S2	Israel	country	continent:Asia:6255147	0	2080	2019-06-30 19:07:07.888641	\N	\N	100
state:EastGrandBahama:8030546	S2	East Grand Bahama	state	country:Bahamas:3572887	1	1516	2019-07-01 03:21:06.239755	\N	\N	100
state:Lekoumou:2258534	S2	Lékoumou	state	country:Congo:2260494	1	2074	2019-06-30 18:40:30.329304	\N	\N	100
state:EastAyrshire:3333226	S2	East Ayrshire	state	region:SouthWestern:2637295	1	515	2019-06-30 18:40:53.273449	\N	\N	100
state:Seti:1282797	S2	Seti	state	region:FarWestern:7289705	1	3343	2019-07-01 16:05:31.237738	\N	\N	100
state:AppenzellInnerrhoden:2661741	S2	Appenzell Innerrhoden	state	country:Switzerland:2658434	1	819	2019-07-02 01:26:58.306142	\N	\N	100
state:Maldonado:3441890	S2	Maldonado	state	country:Uruguay:3439705	1	2551	2019-06-30 22:40:50.778501	\N	\N	100
state:Hertfordshire:2647043	S2	Hertfordshire	state	region:East:2637438	1	1012	2019-07-01 19:41:29.388923	\N	\N	100
strait:TatarStrait:2120711	S2	Tatar Strait	strait	root	1	597	2019-06-30 10:47:36.901012	\N	\N	100
state:Inverclyde:2646127	S2	Inverclyde	state	region:SouthWestern:2637295	1	1012	2019-06-30 18:34:19.982476	\N	\N	100
state:WakeAtoll:4041685	S2	Wake Atoll	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	255	2022-06-10 02:05:56.153143	\N	\N	100
state:Sivas:300617	S2	Sivas	state	country:Turkey:298795	1	5026	2019-06-30 19:03:50.537212	\N	\N	100
region:Kindia:8335088	S2	Kindia	region	country:Guinea:2420477	0	2503	2019-07-01 21:01:06.553722	\N	\N	100
state:LaRioja:3848949	S2	La Rioja	state	country:Argentina:3865483	1	13482	2019-07-01 01:21:54.356656	\N	\N	100
state:MisamisOccidental:1699493	S2	Misamis Occidental	state	region:NorthernMindanao(RegionX):	1	1027	2019-06-30 14:10:10.033053	\N	\N	100
state:Stirling:2636909	S2	Stirling	state	region:Eastern:2650458	1	1039	2019-06-30 18:34:19.982976	\N	\N	100
strait:StraitOfSingapore	S2	Strait of Singapore	strait	root	1	1002	2019-06-30 12:41:03.834191	\N	\N	100
state:Ghadamis:2214432	S2	Ghadamis	state	country:Libya:2215636	1	10631	2019-06-30 17:40:56.292274	\N	\N	100
state:Iasi:675809	S2	Iasi	state	country:Romania:798549	1	1682	2019-06-30 18:45:01.709058	\N	\N	100
state:Jarash:443121	S2	Jarash	state	country:Jordan:248816	1	1015	2019-06-30 19:06:49.914207	\N	\N	100
bay:HangzhouBay:10098466	S2	Hangzhou Bay	bay	root	1	1046	2019-06-30 11:09:13.00944	\N	\N	100
state:WouleuNtem:2396076	S2	Wouleu-Ntem	state	country:Gabon:2400553	1	4766	2019-07-01 18:47:10.483856	\N	\N	100
state:K.Maras:310858	S2	K. Maras	state	country:Turkey:298795	1	2044	2019-06-30 19:06:18.453045	\N	\N	100
state:Maule:3880306	S2	Maule	state	country:Chile:3895114	1	4117	2019-07-01 01:38:05.93034	\N	\N	100
state:Kəlbəcər:586268	S2	Kəlbəcər	state	region:KalbajarLachinEconomicRegion:	1	1977	2019-07-01 16:19:49.648287	\N	\N	100
state:KampongSpoe:1831132	S2	Kâmpóng Spœ	state	country:Cambodia:1831722	1	2452	2019-06-30 12:43:14.973762	\N	\N	100
state:Tahoua:2439374	S2	Tahoua	state	country:Niger:2440476	1	12439	2019-06-30 17:37:38.163991	\N	\N	100
state:Kedah:1733048	S2	Kedah	state	country:Malaysia:1733045	1	2000	2019-07-01 14:26:56.92193	\N	\N	100
state:Kedougou:2244990	S2	Kédougou	state	country:Senegal:2245662	1	3023	2019-07-01 20:57:52.568322	\N	\N	100
state:LotEtGaronne:2997523	S2	Lot-et-Garonne	state	region:Aquitaine:11071620	1	1067	2019-06-30 21:07:18.875879	\N	\N	100
state:Undof:	S2	UNDOF	state	country:Syria:163843	1	1019	2019-06-30 19:05:32.930559	\N	\N	100
state:Alava:3130717	S2	Álava	state	region:PaisVasco:3336903	1	2001	2019-07-01 19:32:25.829666	\N	\N	100
state:VillaClara:3534168	S2	Villa Clara	state	country:Cuba:3562981	1	2096	2019-07-01 03:04:23.734605	\N	\N	100
state:HamgyongNamdo:1877450	S2	Hamgyŏng-namdo	state	country:NorthKorea:1873107	1	3107	2019-06-30 11:40:52.541603	\N	\N	100
state:Choiseul:7280292	S2	Choiseul	state	country:SolomonIslands:2103350	1	493	2019-06-30 18:45:34.922815	\N	\N	100
state:Monaghan:2962567	S2	Monaghan	state	region:Border:	1	1018	2019-06-30 18:35:13.989956	\N	\N	100
state:Likouala:2258431	S2	Likouala	state	country:Congo:2260494	1	6909	2019-06-30 18:23:47.997702	\N	\N	100
state:Namur:2790472	S2	Namur	state	region:Walloon:3337387	1	512	2019-06-30 21:13:42.25366	\N	\N	100
state:RasAlKhaymah:8226061	S2	Ras Al Khaymah	state	country:UnitedArabEmirates:290557	1	1972	2019-07-03 14:38:07.469404	\N	\N	100
state:Manihiki:4035663	S2	Manihiki	state	country:CookIslands:1899402	1	138	2022-06-07 02:57:01.675133	\N	\N	100
gulf:RagayGulf:1691936	S2	Ragay Gulf	gulf	root	1	1536	2019-06-30 14:09:28.158179	\N	\N	100
state:Omagh:11353071	S2	Omagh	state	region:NorthernIreland:2635167	1	1018	2019-06-30 18:35:13.99062	\N	\N	100
state:Lofa:2275344	S2	Lofa	state	country:Liberia:2275384	1	2480	2019-06-30 23:04:37.707775	\N	\N	100
state:Labuan:1734240	S2	Labuan	state	country:Malaysia:1733045	1	1990	2019-07-03 10:31:19.116377	\N	\N	100
state:Zarqa:250092	S2	Zarqa	state	country:Jordan:248816	1	1529	2019-06-30 19:06:49.915163	\N	\N	100
state:Orkney:2640923	S2	Orkney	state	region:HighlandsAndIslands:6621347	1	2085	2019-06-30 18:32:48.124539	\N	\N	100
state:Pardubicky:3339574	S2	Pardubický	state	country:CzechRepublic:3077311	1	2057	2019-06-30 18:21:02.232261	\N	\N	100
country:Togo:2363686	S2	Togo	country	continent:Africa:6255146	0	9640	2019-07-02 00:18:12.735961	\N	\N	100
state:Fermanagh:11353071	S2	Fermanagh	state	region:NorthernIreland:2635167	1	1023	2019-06-30 18:41:17.556642	\N	\N	100
state:AtlanticoSur:3830308	S2	Atlántico Sur	state	country:Nicaragua:3617476	1	5164	2019-07-01 03:05:53.789835	\N	\N	100
state:AtlanticoNorte:3830307	S2	Atlántico Norte	state	country:Nicaragua:3617476	1	6660	2019-07-01 03:14:57.788128	\N	\N	100
state:Kozje:3239243	S2	Kozje	state	region:Savinjska:3186550	1	973	2019-06-30 18:02:47.673132	\N	\N	100
state:St.Eustatius:7610359	S2	St. Eustatius	state	country:Netherlands:2750405	1	1947	2019-06-30 10:10:41.934569	\N	\N	100
state:KampongThum:1831124	S2	Kâmpóng Thum	state	country:Cambodia:1831722	1	1531	2019-06-30 12:33:56.698664	\N	\N	100
state:Bururi:423327	S2	Bururi	state	country:Burundi:433561	1	1122	2019-07-02 15:55:25.426564	\N	\N	100
state:Ordu:741098	S2	Ordu	state	country:Turkey:298795	1	519	2019-06-30 19:12:08.273815	\N	\N	100
state:Osmaniye:443183	S2	Osmaniye	state	country:Turkey:298795	1	1017	2019-06-30 19:06:18.452474	\N	\N	100
state:Tartus:163342	S2	Tartus	state	country:Syria:163843	1	1532	2019-06-30 19:05:07.566145	\N	\N	100
state:MariyEl:529352	S2	Mariy-El	state	region:Volga:11961325	1	3049	2019-06-30 19:04:27.425464	\N	\N	100
state:Kagera:148679	S2	Kagera	state	country:Tanzania:149590	1	4225	2019-07-02 16:25:14.596706	\N	\N	100
state:Istarska:3337514	S2	Istarska	state	country:Croatia:3202326	1	1527	2019-06-30 18:02:41.802431	\N	\N	100
state:Giresun:746878	S2	Giresun	state	country:Turkey:298795	1	2036	2019-06-30 19:04:45.551878	\N	\N	100
state:BasCongo:2317277	S2	Bas-Congo	state	country:Congo:2260494	1	4902	2019-06-30 17:58:59.512213	\N	\N	100
state:Aqaba:443122	S2	Aqaba	state	country:Jordan:248816	1	1557	2019-06-30 19:07:58.9623	\N	\N	100
state:Gunma:1863501	S2	Gunma	state	region:Kanto:1860102	1	2050	2019-06-30 10:13:57.879221	\N	\N	100
state:Benguela:3351660	S2	Benguela	state	country:Angola:3351879	1	4161	2019-06-30 18:41:00.888312	\N	\N	100
state:Loja:3654665	S2	Loja	state	country:Ecuador:3658394	1	1011	2019-07-02 08:28:01.903194	\N	\N	100
state:Tripura:1254169	S2	Tripura	state	region:Northeast:10575550	1	2288	2019-07-01 13:04:35.051901	\N	\N	100
state:Hadarom:294952	S2	HaDarom	state	country:Israel:294640	1	1058	2019-06-30 19:07:07.888147	\N	\N	100
state:Trabzon:738647	S2	Trabzon	state	country:Turkey:298795	1	633	2019-06-30 19:09:17.961704	\N	\N	100
state:SanJose:3621837	S2	San José	state	country:CostaRica:3624060	1	1526	2019-07-01 03:03:22.027805	\N	\N	100
state:Nippes:7115999	S2	Nippes	state	country:Haiti:3723988	1	506	2019-07-02 08:36:36.997415	\N	\N	100
state:Sulu:1685370	S2	Sulu	state	region:AutonomousRegionInMuslimMindanao(Armm):	1	1025	2019-06-30 14:15:07.814643	\N	\N	100
bay:AntongilaBay	S2	Antongila Bay	bay	root	1	536	2019-06-30 15:30:38.790098	\N	\N	100
region:Spodnjeposavska:	S2	Spodnjeposavska	region	country:Slovenia:3190538	0	1738	2019-06-30 18:02:47.675187	\N	\N	100
state:Maradi:2441289	S2	Maradi	state	country:Niger:2440476	1	7304	2019-06-30 17:44:05.756222	\N	\N	100
state:HauteMatsiatra:7670905	S2	Haute Matsiatra	state	country:Madagascar:1062947	1	3116	2019-06-30 15:39:31.266926	\N	\N	100
state:Kayin:1320233	S2	Kayin	state	country:Myanmar:1327865	1	5746	2019-06-30 10:15:06.134401	\N	\N	100
country:Palestine:6254930	S2	Palestine	country	continent:Asia:6255147	0	1038	2019-06-30 19:16:04.295767	\N	\N	100
state:AlMinufiyah:360689	S2	Al Minufiyah	state	country:Egypt:357994	1	2041	2019-07-01 21:31:50.129294	\N	\N	100
state:AnseAuxPins:241450	S2	Anse aux Pins	state	country:Seychelles:241170	1	484	2019-07-04 11:30:56.156334	\N	\N	100
state:Sharjah:292673	S2	Sharjah	state	country:UnitedArabEmirates:290557	1	994	2019-07-03 14:47:49.866777	\N	\N	100
state:Karnten:2774686	S2	Kärnten	state	country:Austria:2782113	1	3047	2019-06-30 18:01:32.31209	\N	\N	100
state:Kouilou:2258738	S2	Kouilou	state	country:Congo:2260494	1	2260	2019-06-30 18:49:24.565882	\N	\N	100
state:Hatay:312394	S2	Hatay	state	country:Turkey:298795	1	1032	2019-06-30 19:13:18.866844	\N	\N	100
state:Ocnita:617656	S2	Ocniţa	state	country:Moldova:617790	1	1023	2019-06-30 19:04:46.040171	\N	\N	100
state:Dhamar:76183	S2	Dhamar	state	country:Yemen:69543	1	2119	2019-06-30 19:54:10.370321	\N	\N	100
state:Chimbu:2098593	S2	Chimbu	state	country:PapuaNewGuinea:2088628	1	1016	2019-06-30 20:07:57.574111	\N	\N	100
region:Abruzzo:3183560	S2	Abruzzo	region	country:Italy:3175395	0	2600	2019-06-30 18:08:29.354792	\N	\N	100
state:Wien:2761367	S2	Wien	state	country:Austria:2782113	1	1702	2019-06-30 18:16:29.280773	\N	\N	100
state:Beirut:276781	S2	Beirut	state	country:Lebanon:272103	1	510	2019-06-30 19:12:45.049397	\N	\N	100
state:LickoSenjska:3337520	S2	Licko-Senjska	state	country:Croatia:3202326	1	2058	2019-06-30 18:19:41.376333	\N	\N	100
state:SouthAndros:8030556	S2	South Andros	state	country:Bahamas:3572887	1	995	2019-07-02 23:58:19.829466	\N	\N	100
state:Drochia:618369	S2	Drochia	state	country:Moldova:617790	1	519	2019-06-30 19:04:46.041078	\N	\N	100
state:AshSharqiyahSouth:411738	S2	Ash Sharqiyah South	state	country:Oman:286963	1	4268	2019-06-30 15:54:31.38656	\N	\N	100
state:NorthernSamar:1697549	S2	Northern Samar	state	region:EasternVisayas(RegionViii):	1	520	2019-06-30 14:12:27.791266	\N	\N	100
strait:MatochkinSharStrait	S2	Matochkin Shar Strait	strait	root	1	1674	2019-06-30 17:44:45.495822	\N	\N	100
state:Luxor:7603259	S2	Luxor	state	country:Egypt:357994	1	515	2019-06-30 19:10:10.358543	\N	\N	100
state:SanAndresYProvidencia:3670205	S2	San Andrés y Providencia	state	country:Colombia:3686110	1	1515	2019-07-01 03:32:48.581003	\N	\N	100
state:AlBuhayrah:361370	S2	Al Buhayrah	state	country:Egypt:357994	1	2039	2019-07-01 21:31:50.13021	\N	\N	100
state:VieuxFort:3576413	S2	Vieux Fort	state	country:SaintLucia:3576468	1	498	2019-07-03 21:43:53.531908	\N	\N	100
state:Tumbes:3691146	S2	Tumbes	state	country:Peru:3932488	1	1512	2019-06-30 10:16:03.471771	\N	\N	100
state:AlBuraymi:7110710	S2	Al Buraymi	state	country:Oman:286963	1	1500	2019-07-03 14:35:22.275936	\N	\N	100
state:Dauphin:3576771	S2	Dauphin	state	country:SaintLucia:3576468	1	500	2019-07-03 21:43:53.532762	\N	\N	100
state:Macerata:3174379	S2	Macerata	state	region:Marche:3174004	1	513	2019-06-30 18:08:29.355554	\N	\N	100
state:Praslin:3576507	S2	Praslin	state	country:SaintLucia:3576468	1	498	2019-07-03 21:43:53.533191	\N	\N	100
state:Sumy:692196	S2	Sumy	state	country:Ukraine:690791	1	4198	2019-07-01 22:34:32.867412	\N	\N	100
state:Iloilo:1711004	S2	Iloilo	state	region:WesternVisayas(RegionVi):	1	513	2019-06-30 14:13:11.368725	\N	\N	100
region:Marche:3174004	S2	Marche	region	country:Italy:3175395	0	2543	2019-06-30 18:01:32.864468	\N	\N	100
state:Pwani:150602	S2	Pwani	state	country:Tanzania:149590	1	2129	2019-06-30 20:17:03.482723	\N	\N	100
state:CuanzaNorte:2241660	S2	Cuanza Norte	state	country:Angola:3351879	1	2897	2019-06-30 18:39:33.16956	\N	\N	100
state:Graubunden:2660522	S2	Graubünden	state	country:Switzerland:2658434	1	1920	2019-07-02 01:54:49.000481	\N	\N	100
state:Shanghai:1796236	S2	Shanghai	state	region:EastChina:6493581	1	1075	2019-06-30 11:09:11.624994	\N	\N	100
state:AmanatAlAsimah:6940571	S2	Amanat Al Asimah	state	country:Yemen:69543	1	1032	2019-06-30 19:32:50.911286	\N	\N	100
state:Qina:350546	S2	Qina	state	country:Egypt:357994	1	1544	2019-06-30 19:10:10.359018	\N	\N	100
state:Djibouti:223818	S2	Djibouti	state	country:Djibouti:223816	1	515	2019-06-30 20:44:14.373322	\N	\N	100
state:Misratah:2214845	S2	Misratah	state	country:Libya:2215636	1	8056	2019-07-01 18:33:23.38001	\N	\N	100
state:Piura:3693525	S2	Piura	state	country:Peru:3932488	1	5077	2019-06-30 10:16:03.471106	\N	\N	100
state:Gulf:2096633	S2	Gulf	state	country:PapuaNewGuinea:2088628	1	5041	2019-06-30 20:06:25.905522	\N	\N	100
state:WestHerzegovina:3343736	S2	West Herzegovina	state	region:FederacijaBosnaIHercegovina:	1	1039	2019-07-02 17:56:37.565942	\N	\N	100
state:Bratislavsky:3343955	S2	Bratislavský	state	country:Slovakia:3057568	1	1024	2019-06-30 18:18:54.312852	\N	\N	100
state:TelAviv:293396	S2	Tel Aviv	state	country:Israel:294640	1	516	2019-06-30 19:21:47.326816	\N	\N	100
state:LoireAtlantique:2997861	S2	Loire-Atlantique	state	region:PaysDeLaLoire:2988289	1	1259	2019-07-01 19:57:08.219457	\N	\N	100
state:Mulanje:925788	S2	Mulanje	state	region:Southern:923817	1	501	2019-06-30 20:03:40.511153	\N	\N	100
state:Aswan:359787	S2	Aswan	state	country:Egypt:357994	1	1579	2019-06-30 19:12:54.197534	\N	\N	100
state:UpperTakutuUpperEssequibo:3375469	S2	Upper Takutu-Upper Essequibo	state	country:Guyana:3378535	1	1999	2019-06-30 23:43:45.646931	\N	\N	100
state:HarariPeople:444184	S2	Harari People	state	country:Ethiopia:337996	1	518	2019-06-30 20:19:28.620828	\N	\N	100
state:EasternHighlands:2097855	S2	Eastern Highlands	state	country:PapuaNewGuinea:2088628	1	2011	2019-06-30 20:07:26.291099	\N	\N	100
state:Dennery:3576764	S2	Dennery	state	country:SaintLucia:3576468	1	499	2019-07-03 21:43:53.533596	\N	\N	100
state:DireDawa:444182	S2	Dire Dawa	state	country:Ethiopia:337996	1	519	2019-06-30 20:19:28.621306	\N	\N	100
state:Alibori:2597271	S2	Alibori	state	country:Benin:2395170	1	3521	2019-06-30 18:00:57.627395	\N	\N	100
state:Nievre:2990371	S2	Nièvre	state	region:Bourgogne:11071619	1	1586	2019-06-30 21:13:24.663671	\N	\N	100
country:SaintLucia:3576468	S2	Saint Lucia	country	continent:NorthAmerica:6255149	0	499	2019-07-03 21:43:53.535262	\N	\N	100
lagoon:Kaliningrad:554230	S2	Kaliningrad	lagoon	root	1	2573	2019-06-30 18:06:32.115783	\N	\N	100
state:Camiguin:1719694	S2	Camiguin	state	region:NorthernMindanao(RegionX):	1	505	2019-07-01 10:30:36.732088	\N	\N	100
state:ParacelIslands:1821073	S2	Paracel Islands	state	country:China:1814991	1	1005	2019-07-04 09:57:57.050603	\N	\N	100
state:Zomba:923295	S2	Zomba	state	region:Southern:923817	1	512	2019-06-30 20:01:56.41501	\N	\N	100
state:Obock:221525	S2	Obock	state	country:Djibouti:223816	1	1034	2019-06-30 20:44:49.607103	\N	\N	100
state:ZanzibarWest:148724	S2	Zanzibar West	state	country:Tanzania:149590	1	513	2019-06-30 20:46:42.445805	\N	\N	100
state:Gironde:3015948	S2	Gironde	state	region:Aquitaine:11071620	1	2042	2019-06-30 21:13:14.863171	\N	\N	100
state:Tarn:2973362	S2	Tarn	state	region:MidiPyrenees:11071623	1	1052	2019-06-30 21:10:19.763923	\N	\N	100
state:SantaCruzDeTenerife:2511173	S2	Santa Cruz de Tenerife	state	region:CanaryIs.:	1	998	2019-06-30 21:28:00.991232	\N	\N	100
state:Moravskoslezsky:3339573	S2	Moravskoslezský	state	country:CzechRepublic:3077311	1	2566	2019-06-30 18:17:27.93032	\N	\N	100
state:Brussels:2800867	S2	Brussels	state	region:CapitalRegion:2800867	1	1014	2019-06-30 21:09:19.72585	\N	\N	100
state:PreahVihear:1822676	S2	Preah Vihéar	state	country:Cambodia:1831722	1	2055	2019-06-30 12:34:25.252559	\N	\N	100
state:NorfolkIsland:2155115	S2	Norfolk Island	state	country:NorfolkIsland:2155115	1	522	2022-06-07 02:54:43.179986	\N	\N	100
state:Ostrobothnia:830667	S2	Ostrobothnia	state	country:Finland:660013	1	2202	2019-06-30 17:59:42.292039	\N	\N	100
country:Brunei:1820814	S2	Brunei	country	continent:Asia:6255147	0	2534	2019-07-03 10:30:02.812783	\N	\N	100
state:LasPalmas:2515271	S2	Las Palmas	state	region:CanaryIs.:	1	2003	2019-06-30 21:28:30.651108	\N	\N	100
state:Sarthe:2975926	S2	Sarthe	state	region:PaysDeLaLoire:2988289	1	1233	2019-06-30 21:09:02.007385	\N	\N	100
state:DarEsSalaam:160260	S2	Dar-Es-Salaam	state	country:Tanzania:149590	1	156	2019-06-30 20:46:48.684859	\N	\N	100
state:Barcelona:3128759	S2	Barcelona	state	region:Cataluna:3336901	1	1033	2019-06-30 21:12:31.075856	\N	\N	100
state:KaskaziniPemba:150733	S2	Kaskazini-Pemba	state	country:Tanzania:149590	1	515	2019-06-30 20:47:56.135262	\N	\N	100
state:Fingal:6693220	S2	Fingal	state	region:Dublin:2964573	1	1012	2019-06-30 21:35:30.599445	\N	\N	100
state:KaskaziniUnguja:148725	S2	Kaskazini-Unguja	state	country:Tanzania:149590	1	513	2019-06-30 20:46:42.446436	\N	\N	100
state:HauteGaronne:3013767	S2	Haute-Garonne	state	region:MidiPyrenees:11071623	1	1101	2019-06-30 21:12:13.29172	\N	\N	100
state:Kochi:1859133	S2	Kōchi	state	region:Shikoku:1852487	1	1507	2019-07-03 08:24:42.922479	\N	\N	100
bay:BightOfBenin:2303181	S2	Bight of Benin	bay	root	1	6664	2019-06-30 17:43:31.949093	\N	\N	100
sea:InnerSeas:8617622	S2	Inner Seas	sea	root	1	12386	2019-06-30 18:32:58.490645	\N	\N	100
state:Flevoland:3319179	S2	Flevoland	state	country:Netherlands:2750405	1	1025	2019-06-30 21:26:44.637517	\N	\N	100
state:Azuay:3660431	S2	Azuay	state	country:Ecuador:3658394	1	1027	2019-07-02 08:22:39.936089	\N	\N	100
state:Gotland:2711508	S2	Gotland	state	country:Sweden:2661886	1	3049	2019-06-30 18:03:06.965571	\N	\N	100
state:Tadjourah:220781	S2	Tadjourah	state	country:Djibouti:223816	1	1039	2019-06-30 20:44:49.607599	\N	\N	100
state:Sligo:2961422	S2	Sligo	state	region:Border:	1	644	2019-06-30 21:36:47.724971	\N	\N	100
state:KusiniPemba:150732	S2	Kusini-Pemba	state	country:Tanzania:149590	1	518	2019-06-30 20:47:56.13591	\N	\N	100
state:Galway:2964179	S2	Galway	state	region:West:8642922	1	955	2019-06-30 21:36:47.727664	\N	\N	100
state:Flores:3442587	S2	Flores	state	country:Uruguay:3439705	1	3053	2019-06-30 22:36:13.24929	\N	\N	100
state:Ondo:2326168	S2	Ondo	state	country:Nigeria:2328926	1	2606	2019-06-30 17:42:51.9204	\N	\N	100
state:Dili:1645456	S2	Dili	state	country:TimorLeste:1966436	1	1012	2019-06-30 10:33:37.754388	\N	\N	100
state:NegrosOccidental:1697808	S2	Negros Occidental	state	region:WesternVisayas(RegionVi):	1	1584	2019-06-30 14:13:46.150743	\N	\N	100
state:QuangNam:1905516	S2	Quàng Nam	state	country:Vietnam:1562822	1	3064	2019-06-30 12:41:19.260212	\N	\N	100
state:Rajshahi:1337166	S2	Rajshahi	state	country:Bangladesh:1210997	1	2533	2019-07-01 13:06:10.33879	\N	\N	100
state:Puntarenas:3622226	S2	Puntarenas	state	country:CostaRica:3624060	1	2052	2019-07-01 03:10:33.231903	\N	\N	100
gulf:GulfOfGabes:2468368	S2	Gulf of Gabès	gulf	root	1	1027	2019-06-30 18:17:12.555094	\N	\N	100
state:Nagaland:1262271	S2	Nagaland	state	region:Northeast:10575550	1	2073	2019-06-30 17:08:42.441004	\N	\N	100
country:BritishIndianOceanTerritory:1282588	S2	British Indian Ocean Territory	country	continent:SevenSeas(OpenOcean):	0	1991	2019-07-01 17:15:08.086881	\N	\N	100
state:SaintPeter:3578039	S2	Saint Peter	state	country:Montserrat:3578097	1	514	2019-07-02 00:09:40.297506	\N	\N	100
state:Lankaran:147622	S2	Lankaran	state	region:LankaranEconomicRegion:	1	1890	2019-06-30 20:06:10.945844	\N	\N	100
state:Huancavelica:3939467	S2	Huancavelica	state	country:Peru:3932488	1	4341	2019-07-01 02:16:39.632825	\N	\N	100
state:SaintGeorge:3373572	S2	Saint George	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.472296	\N	\N	100
state:Bafing:2597334	S2	Bafing	state	country:IvoryCoast:2287781	1	1539	2019-06-30 22:27:11.010858	\N	\N	100
state:QuangBinh:1568839	S2	Quảng Bình	state	region:BacTrungBo:11497251	1	1076	2019-06-30 12:39:44.953864	\N	\N	100
state:Azores:3411865	S2	Azores	state	country:Portugal:2264397	1	1530	2019-06-30 21:39:06.283358	\N	\N	100
state:Denguele:2597328	S2	Denguélé	state	country:IvoryCoast:2287781	1	2570	2019-06-30 21:54:43.981826	\N	\N	100
state:SouthTipperary:2961189	S2	South Tipperary	state	region:SouthEast:	1	661	2019-06-30 21:42:09.471807	\N	\N	100
state:RioNegro:3440789	S2	Río Negro	state	country:Uruguay:3439705	1	1934	2019-06-30 22:38:38.324714	\N	\N	100
state:Tarija:3903319	S2	Tarija	state	country:Bolivia:3923057	1	4096	2019-07-01 01:48:28.541089	\N	\N	100
state:Ainaro:1651809	S2	Ainaro	state	country:TimorLeste:1966436	1	1013	2019-06-30 10:33:26.682642	\N	\N	100
gulf:GulfOfSuez:235614	S2	Gulf of Suez	gulf	root	1	3152	2019-06-30 19:07:02.157524	\N	\N	100
country:MarshallIslands:2080185	S2	Marshall Islands	country	continent:Oceania:6255151	0	1004	2019-06-30 10:10:32.919141	\N	\N	100
state:Luanda:2240444	S2	Luanda	state	country:Angola:3351879	1	1542	2019-06-30 18:19:09.237515	\N	\N	100
region:Kankan:8335087	S2	Kankan	region	country:Guinea:2420477	0	7555	2019-06-30 21:58:25.398489	\N	\N	100
state:Soriano:3440054	S2	Soriano	state	country:Uruguay:3439705	1	2540	2019-06-30 22:51:04.32132	\N	\N	100
state:NovoMesto:3194350	S2	Novo Mesto	state	region:JugovzhodnaSlovenija:	1	969	2019-06-30 18:02:47.673957	\N	\N	100
state:Jiangxi:1806222	S2	Jiangxi	state	region:EastChina:6493581	1	18193	2019-07-01 00:32:39.854717	\N	\N	100
state:Neftcala:147422	S2	Neftçala	state	region:AranEconomicRegion:	1	502	2019-06-30 20:06:10.946807	\N	\N	100
state:Gbapolu:2593119	S2	Gbapolu	state	country:Liberia:2275384	1	2466	2019-06-30 23:02:34.116093	\N	\N	100
state:LeewardIslands:4034364	S2	Leeward Islands	state	country:FrenchPolynesia:4030656	1	975	2019-07-01 07:43:10.104078	\N	\N	100
region:Hokkaido:2130037	S2	Hokkaido	region	country:Japan:1861060	0	12161	2019-06-30 10:10:40.764548	\N	\N	100
state:GoviSumber:	S2	Govĭ-Sümber	state	country:Mongolia:2029969	1	2066	2019-06-30 23:22:23.312334	\N	\N	100
state:Ardennes:3037136	S2	Ardennes	state	region:ChampagneArdenne:11071622	1	2054	2019-06-30 21:12:16.868073	\N	\N	100
state:Liquica:1637729	S2	Liquica	state	country:TimorLeste:1966436	1	507	2019-06-30 10:33:57.259666	\N	\N	100
state:Mayo:2962666	S2	Mayo	state	region:West:8642922	1	1637	2019-06-30 21:38:56.925522	\N	\N	100
state:Niari:2256175	S2	Niari	state	country:Congo:203312	1	2073	2019-06-30 18:22:40.700799	\N	\N	100
state:Risaralda:3670698	S2	Risaralda	state	country:Colombia:3686110	1	2528	2019-07-02 08:23:29.919743	\N	\N	100
state:NorthTipperary:2961190	S2	North Tipperary	state	region:MidWest:	1	162	2019-06-30 21:42:09.472751	\N	\N	100
state:Burgos:3127460	S2	Burgos	state	region:CastillaYLeon:3336900	1	2049	2019-07-01 19:32:25.831543	\N	\N	100
state:Vestfold:3132015	S2	Vestfold	state	country:Norway:3144096	1	1919	2019-06-30 21:10:15.927665	\N	\N	100
state:Casanare:3687173	S2	Casanare	state	country:Colombia:3686110	1	4731	2019-07-01 01:13:24.617036	\N	\N	100
state:Sergipe:3447799	S2	Sergipe	state	country:Brazil:3469034	1	2068	2019-06-30 21:54:46.268715	\N	\N	100
state:Laoighis:2963031	S2	Laoighis	state	region:Midlands:	1	1030	2019-06-30 21:42:11.036576	\N	\N	100
state:Nord:2973357	S2	Nord	state	region:NordPasDeCalais:11071624	1	1030	2019-06-30 21:12:16.868584	\N	\N	100
state:Kerry:2963517	S2	Kerry	state	region:SouthWest:	1	2050	2019-06-30 21:43:05.320192	\N	\N	100
state:Beyla:2423124	S2	Beyla	state	region:Nzerekore:8335091	1	1012	2019-06-30 22:59:18.433789	\N	\N	100
state:Ermera:1644865	S2	Ermera	state	country:TimorLeste:1966436	1	506	2019-06-30 10:33:57.260914	\N	\N	100
state:Aileu:1651815	S2	Aileu	state	country:TimorLeste:1966436	1	509	2019-06-30 10:33:57.262068	\N	\N	100
state:Krsko:3197146	S2	Krško	state	region:Spodnjeposavska:	1	969	2019-06-30 18:02:47.674777	\N	\N	100
strait:TorresStrait:7839398	S2	Torres Strait	strait	root	1	2526	2019-06-30 20:08:17.504667	\N	\N	100
gulf:GulfOfAqaba:11205522	S2	Gulf of Aqaba	gulf	root	1	1033	2019-06-30 19:07:02.178036	\N	\N	100
region:NordPasDeCalais:11071624	S2	Nord-Pas-de-Calais	region	country:France:3017382	0	3302	2019-06-30 21:10:03.549918	\N	\N	100
state:Worodougou:2597327	S2	Worodougou	state	country:IvoryCoast:2287781	1	3106	2019-06-30 22:57:34.87295	\N	\N	100
state:NegrosOriental:1697806	S2	Negros Oriental	state	region:CentralVisayas(RegionVii):	1	697	2019-06-30 14:17:45.452942	\N	\N	100
region:Koroska:3217862	S2	Koroška	region	country:Slovenia:3190538	0	1274	2019-06-30 18:07:32.983221	\N	\N	100
state:Mandiana:2595305	S2	Mandiana	state	region:Kankan:8335087	1	1027	2019-06-30 22:03:19.826557	\N	\N	100
state:ValleDelCauca:3666313	S2	Valle del Cauca	state	country:Colombia:3686110	1	3562	2019-07-02 08:23:29.920245	\N	\N	100
state:Bouenza:2260668	S2	Bouenza	state	country:Congo:203312	1	1038	2019-06-30 18:22:40.701441	\N	\N	100
country:Liechtenstein:3042058	S2	Liechtenstein	country	continent:Europe:6255148	0	507	2019-07-02 01:26:58.304343	\N	\N	100
state:Yomou:2414077	S2	Yomou	state	region:Nzerekore:8335091	1	503	2019-06-30 23:04:37.7065	\N	\N	100
state:Margibi:2588491	S2	Margibi	state	country:Liberia:2275384	1	1479	2019-06-30 22:37:41.189492	\N	\N	100
state:RioGrandeDoNorte:3390290	S2	Rio Grande do Norte	state	country:Brazil:3469034	1	8545	2019-06-30 21:52:46.477332	\N	\N	100
state:Saida:2482557	S2	Saïda	state	country:Algeria:2589581	1	2106	2019-06-30 22:43:21.430186	\N	\N	100
channel:DixonEntrance:5940320	S2	Dixon Entrance	channel	root	1	1999	2019-07-01 08:45:12.200567	\N	\N	100
state:Caaguazu:3439312	S2	Caaguazú	state	country:Paraguay:3437598	1	1955	2019-06-30 22:36:32.223205	\N	\N	100
state:DytikiEllada:6697810	S2	Dytiki Ellada	state	region:Peloponnisos:6697807	1	1038	2019-07-01 18:52:36.234002	\N	\N	100
state:Fukushima:2112922	S2	Fukushima	state	region:Tohoku:2110769	1	1608	2019-06-30 10:14:32.128231	\N	\N	100
bay:StHelenaBay	S2	St Helena Bay	bay	root	1	2967	2019-06-30 20:12:08.414209	\N	\N	100
state:CerroLargo:3443173	S2	Cerro Largo	state	country:Uruguay:3439705	1	5112	2019-06-30 22:35:48.894898	\N	\N	100
state:Betsiboka:7670850	S2	Betsiboka	state	country:Madagascar:1062947	1	3129	2019-06-30 15:42:14.827869	\N	\N	100
state:SanJose:3440645	S2	San José	state	country:Uruguay:3439705	1	1019	2019-06-30 22:38:20.717436	\N	\N	100
state:Montevideo:3441572	S2	Montevideo	state	country:Uruguay:3439705	1	508	2019-06-30 22:42:44.157998	\N	\N	100
state:Geita:8469239	S2	Geita	state	country:Tanzania:149590	1	3234	2019-07-04 14:27:44.198298	\N	\N	100
gulf:GulfStVincent:8162475	S2	Gulf St Vincent	gulf	root	1	8676	2019-06-30 19:50:56.51984	\N	\N	100
state:Rivera:3440780	S2	Rivera	state	country:Uruguay:3439705	1	1316	2019-06-30 22:29:19.164379	\N	\N	100
state:Nan:1608451	S2	Nan	state	region:Northern:6695803	1	3083	2019-07-01 15:05:19.347138	\N	\N	100
state:Florida:3442584	S2	Florida	state	country:Uruguay:3439705	1	1039	2019-06-30 22:28:28.435409	\N	\N	100
state:Kampot:1831111	S2	Kâmpôt	state	country:Cambodia:1831722	1	2987	2019-06-30 12:43:14.97283	\N	\N	100
state:Bamako:2460594	S2	Bamako	state	country:Mali:2453866	1	2014	2019-06-30 22:00:31.42037	\N	\N	100
state:Vakinankaratra:7670854	S2	Vakinankaratra	state	country:Madagascar:1062947	1	3673	2019-06-30 15:42:45.75868	\N	\N	100
state:Monagas:3632100	S2	Monagas	state	country:Venezuela:3625428	1	2970	2019-07-02 00:02:54.002815	\N	\N	100
state:Chiriqui:3712410	S2	Chiriquí	state	country:Panama:3703430	1	2024	2019-07-01 03:14:10.499595	\N	\N	100
state:NorthSomerset:3333177	S2	North Somerset	state	region:SouthEast:2637438	1	206	2022-05-09 19:29:57.358996	\N	\N	100
strait:VilKitskogoStrait	S2	Vil'kitskogo Strait	strait	root	1	7453	2019-06-30 14:06:12.191397	\N	\N	100
state:Kirsehir:307513	S2	Kirsehir	state	country:Turkey:298795	1	1168	2019-07-01 22:37:00.196043	\N	\N	100
state:Artigas:3443756	S2	Artigas	state	country:Uruguay:3439705	1	1987	2019-06-30 22:28:19.9661	\N	\N	100
state:Durazno:3442720	S2	Durazno	state	country:Uruguay:3439705	1	1040	2019-06-30 22:36:13.249807	\N	\N	100
gulf:GulfOfMartaban:1310459	S2	Gulf of Martaban	gulf	root	1	4139	2019-06-30 10:14:46.562542	\N	\N	100
state:Ormoz:3193964	S2	Ormož	state	region:Pomurska:	1	766	2019-06-30 18:07:32.983653	\N	\N	100
state:AricaYParinacota:6693562	S2	Arica y Parinacota	state	country:Chile:3895114	1	1560	2019-07-02 01:00:19.891783	\N	\N	100
state:AnGiang:1594446	S2	An Giang	state	region:DongBangSongCuuLong:1574717	1	500	2019-06-30 13:01:11.036257	\N	\N	100
state:Kanchanaburi:1153080	S2	Kanchanaburi	state	region:Western:6470693	1	899	2019-07-04 11:32:44.402793	\N	\N	100
state:Sowa:7761259	S2	Sowa	state	country:Botswana:933860	1	505	2019-07-02 17:29:35.118123	\N	\N	100
state:Tianjin:1792943	S2	Tianjin	state	region:NorthChina:1807463	1	967	2019-07-01 00:49:11.55074	\N	\N	100
state:Kachin:1321702	S2	Kachin	state	country:Myanmar:1327865	1	13186	2019-06-30 10:14:47.487292	\N	\N	100
gulf:RioDeLaPlata:3988613	S2	Río de la Plata	gulf	root	1	4356	2019-06-30 22:37:29.692299	\N	\N	100
state:OuhamPende:2383650	S2	Ouham-Pendé	state	country:CentralAfricanRepublic:239880	1	2863	2019-06-30 17:59:55.577094	\N	\N	100
state:Beijing:2038349	S2	Beijing	state	region:NorthChina:1807463	1	1540	2019-07-01 00:47:36.138986	\N	\N	100
state:Peravia:3495015	S2	Peravia	state	country:DominicanRepublic:3508796	1	1030	2019-06-30 23:42:24.683101	\N	\N	100
state:Phetchaburi:1151416	S2	Phetchaburi	state	region:Western:6470693	1	767	2019-07-01 14:50:54.19415	\N	\N	100
country:Dhekelia:8306814	S2	Dhekelia	country	continent:Asia:6255147	0	517	2019-07-03 15:32:25.897106	\N	\N	100
state:Jujuy:3853404	S2	Jujuy	state	country:Argentina:3865483	1	8647	2019-07-01 01:37:13.708052	\N	\N	100
state:AustAgder:3162354	S2	Aust-Agder	state	country:Norway:3144096	1	3105	2019-06-30 21:11:32.41439	\N	\N	100
state:Nimroz:1131821	S2	Nimroz	state	country:Afghanistan:1149361	1	4327	2019-06-30 14:48:18.220898	\N	\N	100
state:Agneby:2597318	S2	Agnéby	state	country:IvoryCoast:2287781	1	1047	2019-06-30 09:51:44.227699	\N	\N	100
state:Eskisehir:315201	S2	Eskisehir	state	country:Turkey:298795	1	4103	2019-07-01 21:09:42.963176	\N	\N	100
region:Melilla:7577101	S2	Melilla	region	country:Spain:2510769	0	2037	2019-06-30 23:02:05.136166	\N	\N	100
region:WesternVisayas(RegionVi):	S2	Western Visayas (Region VI)	region	country:Philippines:1694008	0	2265	2019-06-30 14:13:11.369216	\N	\N	100
state:Lagunes:2597316	S2	Lagunes	state	country:IvoryCoast:2287781	1	1596	2019-06-30 09:51:44.228285	\N	\N	100
state:Barinas:3648544	S2	Barinas	state	country:Venezuela:3625428	1	3735	2019-07-01 01:12:28.458028	\N	\N	100
state:Zadarska:3337530	S2	Zadarska	state	country:Croatia:3202326	1	2058	2019-06-30 18:19:56.007192	\N	\N	100
region:CentralTransdanubia:	S2	Central Transdanubia	region	country:Hungary:719819	0	2611	2019-07-02 17:55:35.517903	\N	\N	100
country:Benin:2395170	S2	Benin	country	continent:Africa:6255146	0	12096	2019-06-30 18:00:20.974719	\N	\N	100
state:Cuvette:2260487	S2	Cuvette	state	country:Congo:2260494	1	4725	2019-06-30 18:10:53.389244	\N	\N	100
state:BerryIslands:3571809	S2	Berry Islands	state	country:Bahamas:3572887	1	999	2019-07-02 23:55:36.421608	\N	\N	100
region:JugovzhodnaSlovenija:	S2	Jugovzhodna Slovenija	region	country:Croatia:3202326	0	2788	2019-06-30 18:01:16.448312	\N	\N	100
state:Lara:3636539	S2	Lara	state	country:Venezuela:3625428	1	3104	2019-07-01 01:12:08.481152	\N	\N	100
state:HatoMayor:3504766	S2	Hato Mayor	state	country:DominicanRepublic:3508796	1	1036	2019-06-30 23:44:43.898896	\N	\N	100
state:Mogila:863488	S2	Mogila	state	country:Macedonia:718075	1	942	2019-07-01 19:00:20.01759	\N	\N	100
state:SaintAnthony:3578045	S2	Saint Anthony	state	country:Montserrat:3578097	1	514	2019-07-02 00:09:40.297982	\N	\N	100
state:Izmir:311044	S2	Izmir	state	country:Turkey:298795	1	2168	2019-07-02 16:37:27.934217	\N	\N	100
state:Colonia:3443025	S2	Colonia	state	country:Uruguay:3439705	1	1533	2019-06-30 22:51:04.321954	\N	\N	100
state:Trujillo:3625974	S2	Trujillo	state	country:Venezuela:3625428	1	524	2019-07-01 01:15:59.170533	\N	\N	100
state:Bimini:3572807	S2	Bimini	state	country:Bahamas:3572887	1	523	2019-07-01 03:05:48.518066	\N	\N	100
state:Charente:3026646	S2	Charente	state	region:PoitouCharentes:11071620	1	1447	2019-06-30 21:13:08.58646	\N	\N	100
state:Tachira:3626553	S2	Táchira	state	country:Venezuela:3625428	1	1559	2019-07-01 01:13:31.175526	\N	\N	100
state:Arauca:3689717	S2	Arauca	state	country:Colombia:3686110	1	2168	2019-07-01 01:12:34.721532	\N	\N	100
state:Huanuco:3696416	S2	Huánuco	state	country:Peru:3932488	1	5308	2019-07-01 00:53:23.946524	\N	\N	100
bay:UchiuraBay:1849453	S2	Uchiura Bay	bay	root	1	4223	2019-06-30 10:14:19.745917	\N	\N	100
state:Samana:3493232	S2	Samaná	state	country:DominicanRepublic:3508796	1	518	2019-06-30 23:45:16.049012	\N	\N	100
state:Lola:2595304	S2	Lola	state	region:Nzerekore:8335091	1	159	2019-06-30 22:58:58.455605	\N	\N	100
state:Ifugao:1711331	S2	Ifugao	state	region:CordilleraAdministrativeRegion(Car):	1	513	2019-07-02 11:01:00.340817	\N	\N	100
state:Nord:2223603	S2	Nord	state	country:Cameroon:2233387	1	9127	2019-06-30 18:46:36.42659	\N	\N	100
state:Offaly:2962187	S2	Offaly	state	region:Midlands:	1	669	2019-06-30 21:42:09.470425	\N	\N	100
state:GraciasADios:3609583	S2	Gracias a Dios	state	country:Honduras:3608932	1	2572	2019-07-01 03:43:12.391772	\N	\N	100
state:Maluku:1636627	S2	Maluku	state	country:Indonesia:1643084	1	5035	2019-06-30 10:10:38.383161	\N	\N	100
state:Meath:2962661	S2	Meath	state	region:MidEast:	1	1012	2019-06-30 21:35:30.602662	\N	\N	100
state:Gombe:2595347	S2	Gombe	state	country:Nigeria:2328926	1	3057	2019-07-01 18:47:57.024823	\N	\N	100
country:ClippertonIsland:4020092	S2	Clipperton Island	country	continent:SevenSeas(OpenOcean):	0	475	2019-07-01 04:36:45.091071	\N	\N	100
state:Sagaing:1298480	S2	Sagaing	state	country:Myanmar:1327865	1	12167	2019-06-30 17:00:18.383951	\N	\N	100
state:Zaragoza:3104323	S2	Zaragoza	state	region:Aragon:3336899	1	2589	2019-06-30 21:18:01.155431	\N	\N	100
state:PitcairnIslands:4030699	S2	Pitcairn Islands	state	country:PitcairnIslands:4030699	1	492	2019-07-01 04:44:27.731936	\N	\N	100
region:LaRioja:3336897	S2	La Rioja	region	country:Spain:2510769	0	1394	2019-07-01 19:31:58.653737	\N	\N	100
bay:GubaGusinaya:2125413	S2	Guba Gusinaya	bay	root	1	5098	2019-06-30 11:54:17.860997	\N	\N	100
lagoon:LagoDeMaracaibo:3633005	S2	Lago de Maracaibo	lagoon	root	1	2100	2019-07-01 01:14:10.23088	\N	\N	100
state:Rason:2044245	S2	Rasŏn	state	country:NorthKorea:1873107	1	3024	2019-07-01 03:51:17.929325	\N	\N	100
state:Miyagi:2111888	S2	Miyagi	state	region:Tohoku:2110769	1	1455	2019-06-30 10:14:42.981288	\N	\N	100
state:OgooueLolo:2396925	S2	Ogooué-Lolo	state	country:Gabon:2400553	1	3574	2019-07-03 17:49:48.048404	\N	\N	100
state:PalmyraAtoll:5854952	S2	Palmyra Atoll	state	country:UnitedStatesMinorOutlyingIslands:5854968	1	131	2022-06-18 00:01:26.714348	\N	\N	100
state:Chuy:1538652	S2	Chuy	state	country:Kyrgyzstan:1527747	1	2784	2019-06-30 16:37:43.809518	\N	\N	100
region:Savinjska:3186550	S2	Savinjska	region	country:Slovenia:3190538	0	1288	2019-06-30 18:07:32.984879	\N	\N	100
state:Heredia:3623484	S2	Heredia	state	country:CostaRica:3624060	1	507	2019-07-01 03:42:12.549067	\N	\N	100
state:Kyoto:1857907	S2	Kyōto	state	region:Kinki:1859449	1	1249	2019-06-30 10:33:27.503441	\N	\N	100
state:Tavush:828265	S2	Tavush	state	country:Armenia:174982	1	988	2019-07-01 16:10:46.966333	\N	\N	100
river:ColumbiaRiver:5926219	S2	Columbia River	river	root	1	505	2019-07-01 05:11:20.99003	\N	\N	100
state:MangroveCay:8030549	S2	Mangrove Cay	state	country:Bahamas:3572887	1	1003	2019-07-02 23:58:19.828903	\N	\N	100
lagoon:BrasDOrLake:5908033	S2	Bras d'Or Lake	lagoon	root	1	903	2019-06-30 23:39:40.891209	\N	\N	100
fjord:Vestfjorden:776954	S2	Vestfjorden	fjord	root	1	6560	2019-06-30 21:06:54.703745	\N	\N	100
state:LosRios:6693563	S2	Los Ríos	state	country:Chile:3895114	1	3197	2019-07-01 01:37:40.092027	\N	\N	100
state:Canakkale:749778	S2	Çanakkale	state	country:Turkey:298795	1	1202	2019-06-30 18:43:18.640231	\N	\N	100
state:Bohinj:3239101	S2	Bohinj	state	region:Gorenjska:	1	1014	2019-06-30 18:07:54.924484	\N	\N	100
state:SurigaoDelNorte:1685215	S2	Surigao del Norte	state	region:DinagatIslands(RegionXiii):	1	513	2019-07-01 10:40:56.641708	\N	\N	100
state:Lavalleja:3442007	S2	Lavalleja	state	country:Uruguay:3439705	1	2085	2019-06-30 22:28:28.4359	\N	\N	100
state:Tabasco:3516458	S2	Tabasco	state	country:Mexico:3996063	1	4008	2019-07-01 07:58:06.609809	\N	\N	100
state:Chiesanuova:3178807	S2	Chiesanuova	state	country:SanMarino:3345302	1	1015	2019-06-30 18:01:32.857019	\N	\N	100
state:Gorj:676898	S2	Gorj	state	country:Romania:798549	1	3050	2019-07-01 18:31:02.031072	\N	\N	100
state:IlesLoyaute:7521415	S2	Îles Loyauté	state	country:NewCaledonia:2139685	1	1498	2019-07-01 08:36:02.520819	\N	\N	100
gulf:GulfOfRiga:453746	S2	Gulf of Riga	gulf	root	1	7351	2019-06-30 18:03:43.575431	\N	\N	100
state:Bromley:3333135	S2	Bromley	state	region:GreaterLondon:2648110	1	2025	2019-07-01 19:29:18.826967	\N	\N	100
state:Callao:3946080	S2	Callao	state	country:Peru:3932488	1	1021	2019-07-01 02:16:43.562588	\N	\N	100
state:ExtremeNord:2231755	S2	Extrême-Nord	state	country:Cameroon:2233387	1	3437	2019-07-03 18:32:22.569192	\N	\N	100
state:RioSanJuan:3617056	S2	Rio San Juan	state	country:Nicaragua:3617476	1	1536	2019-07-01 03:05:53.790303	\N	\N	100
bay:ShermanBasin:6146208	S2	Sherman Basin	bay	root	1	1085	2019-07-01 08:01:55.312235	\N	\N	100
state:Tafea:2134739	S2	Tafea	state	country:Vanuatu:2134431	1	1481	2019-07-01 08:33:40.120806	\N	\N	100
state:Raymah:71532	S2	Raymah	state	country:Yemen:69543	1	523	2019-06-30 19:56:48.832844	\N	\N	100
state:Kosrae:2082036	S2	Kosrae	state	country:Micronesia:2081918	1	950	2019-07-01 08:36:45.44763	\N	\N	100
country:UnitedArabEmirates:290557	S2	United Arab Emirates	country	continent:Asia:6255147	0	9732	2019-07-01 19:19:05.971877	\N	\N	100
state:CentralSingapore:7535954	S2	Central Singapore	state	country:Singapore:1880251	1	501	2019-06-30 12:41:03.837106	\N	\N	100
state:NorthLanarkshire:3333233	S2	North Lanarkshire	state	region:SouthWestern:2637295	1	1059	2019-06-30 18:33:53.195756	\N	\N	100
state:Curepipe:934570	S2	Curepipe	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.754845	\N	\N	100
state:CityOfFreeport:3572374	S2	City of Freeport	state	country:Bahamas:3572887	1	1026	2019-07-01 03:21:06.239118	\N	\N	100
state:HawkeSBay:2190146	S2	Hawke's Bay	state	region:NorthIsland:2185983	1	2006	2019-07-01 07:43:08.065961	\N	\N	100
state:Almeria:2521883	S2	Almería	state	region:Andalucia:2593109	1	1949	2019-06-30 22:39:32.041344	\N	\N	100
state:Matanzas:3547394	S2	Matanzas	state	country:Cuba:3562981	1	2096	2019-07-01 03:15:41.031955	\N	\N	100
state:Parma:3171456	S2	Parma	state	region:EmiliaRomagna:3177401	1	668	2019-07-02 01:53:31.222534	\N	\N	100
state:Ghor:1141103	S2	Ghor	state	country:Afghanistan:1149361	1	5516	2019-07-02 14:02:00.695454	\N	\N	100
lagoon:HuskyLakes	S2	Husky Lakes	lagoon	root	1	3080	2019-07-01 08:15:48.891916	\N	\N	100
state:NationalCapitalDistrict:2089856	S2	National Capital District	state	country:PapuaNewGuinea:2088628	1	497	2019-07-01 10:13:22.290817	\N	\N	100
bay:DelawareBay:2191814	S2	Delaware Bay	bay	root	1	1024	2019-07-01 02:08:32.093472	\N	\N	100
state:Selenge:2029432	S2	Selenge	state	country:Mongolia:2029969	1	7693	2019-06-30 13:34:40.521975	\N	\N	100
state:Isabel:2108262	S2	Isabel	state	country:SolomonIslands:2103350	1	1496	2019-07-01 08:55:18.124257	\N	\N	100
sound:LongIslandSound:6059135	S2	Long Island Sound	sound	root	1	985	2019-07-03 01:46:09.220012	\N	\N	100
state:SanMarino:3345302	S2	San Marino	state	country:SanMarino:3345302	1	1015	2019-06-30 18:01:32.858951	\N	\N	100
state:SanctiSpiritus:3540665	S2	Sancti Spíritus	state	country:Cuba:3562981	1	1043	2019-07-01 03:19:52.513575	\N	\N	100
state:Caserta:3179865	S2	Caserta	state	region:Campania:3181042	1	1036	2019-06-30 18:17:52.001158	\N	\N	100
state:Sarangani:7671047	S2	Sarangani	state	region:Soccsksargen(RegionXii):	1	519	2019-07-01 10:34:36.19467	\N	\N	100
state:Hokkaido:2130037	S2	Hokkaidō	state	region:Hokkaido:2130037	1	12788	2019-06-30 10:10:40.763931	\N	\N	100
state:Tutong:1820068	S2	Tutong	state	country:Brunei:1820814	1	1491	2019-07-03 10:30:02.810878	\N	\N	100
state:Yap:2081175	S2	Yap	state	country:Micronesia:2081918	1	477	2019-07-01 09:29:05.20768	\N	\N	100
state:CompostelaValley:1717055	S2	Compostela Valley	state	region:Davao(RegionXi):	1	529	2019-07-01 10:39:06.546161	\N	\N	100
state:SamdrupJongkhar:1337290	S2	Samdrup Jongkhar	state	region:Eastern:	1	511	2019-07-01 13:08:54.978436	\N	\N	100
state:Adan:80412	S2	`Adan	state	country:Yemen:69543	1	1029	2019-07-02 15:37:06.977948	\N	\N	100
state:Sylhet:1477362	S2	Sylhet	state	country:Bangladesh:1210997	1	1510	2019-07-01 13:04:08.056587	\N	\N	100
gulf:LeyteGulf:1706798	S2	Leyte Gulf	gulf	root	1	1023	2019-07-01 10:34:42.97258	\N	\N	100
state:Fujian:1811017	S2	Fujian	state	country:China:1814991	1	10883	2019-07-01 11:54:17.917465	\N	\N	100
state:Ulaanbaatar:2028461	S2	Ulaanbaatar	state	country:Mongolia:2029969	1	2566	2019-06-30 23:24:23.030997	\N	\N	100
state:Bago:1300463	S2	Bago	state	country:Myanmar:1327865	1	7552	2019-06-30 10:14:48.84428	\N	\N	100
state:RheinlandPfalz:2847618	S2	Rheinland-Pfalz	state	country:Germany:2921044	1	5230	2019-07-02 19:02:47.576647	\N	\N	100
state:ElOro:3658195	S2	El Oro	state	country:Ecuador:3658394	1	1502	2019-07-02 08:22:39.935565	\N	\N	100
region:Eastern:	S2	Eastern	region	country:Bhutan:1252634	0	1026	2019-07-01 13:08:54.979047	\N	\N	100
state:Tucuman:3833578	S2	Tucumán	state	country:Argentina:3865483	1	3220	2019-07-01 01:48:54.594785	\N	\N	100
state:Meghalaya:1263207	S2	Meghalaya	state	region:Northeast:10575550	1	3043	2019-07-01 13:00:53.395729	\N	\N	100
gulf:YeniseyGulf	S2	Yenisey Gulf	gulf	root	1	7589	2019-06-30 14:09:17.06874	\N	\N	100
strait:SurigaoStrait:1685212	S2	Surigao Strait	strait	root	1	536	2019-07-01 10:40:56.640855	\N	\N	100
state:Highland:2646944	S2	Highland	state	region:HighlandsAndIslands:6621347	1	6643	2019-06-30 18:32:33.465772	\N	\N	100
state:Tokushima:1850157	S2	Tokushima	state	region:Shikoku:1852487	1	510	2019-07-03 08:39:36.270474	\N	\N	100
state:Koulikoro:2454532	S2	Koulikoro	state	country:Mali:2453866	1	10850	2019-06-30 21:59:56.333568	\N	\N	100
state:Raski:7581807	S2	Raški	state	country:Serbia:6290252	1	1005	2019-07-01 18:52:42.495803	\N	\N	100
state:EureEtLoir:3019316	S2	Eure-et-Loir	state	region:Centre:3027939	1	2544	2019-06-30 21:09:02.008277	\N	\N	100
state:EasternSamar:1713986	S2	Eastern Samar	state	region:EasternVisayas(RegionViii):	1	503	2019-07-01 10:34:42.973093	\N	\N	100
state:Yobe:2597367	S2	Yobe	state	country:Nigeria:2328926	1	5128	2019-07-01 18:53:29.137086	\N	\N	100
state:CrossRiver:2345891	S2	Cross River	state	country:Nigeria:2328926	1	1552	2019-07-04 15:43:51.02351	\N	\N	100
state:F.A.T.A.:1179245	S2	F.A.T.A.	state	country:Pakistan:1168579	1	3366	2019-07-01 15:24:28.831462	\N	\N	100
region:Molise:3173222	S2	Molise	region	country:Italy:3175395	0	1554	2019-06-30 18:20:03.587834	\N	\N	100
state:WangdiPhodrang:1337295	S2	Wangdi Phodrang	state	region:Central:	1	1485	2019-07-01 13:00:26.485773	\N	\N	100
state:VacoasPhoenix:933945	S2	Vacoas-Phoenix	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.755297	\N	\N	100
state:WestGrandBahama:8030559	S2	West Grand Bahama	state	country:Bahamas:3572887	1	1027	2019-07-01 03:21:06.240321	\N	\N	100
state:Alagoas:3408096	S2	Alagoas	state	country:Brazil:3469034	1	4096	2019-06-30 21:54:46.268264	\N	\N	100
state:LaReunion:2972191	S2	La Réunion	state	region:Reunion:2972191	1	486	2019-07-01 14:21:59.411922	\N	\N	100
state:Lattakia:173576	S2	Lattakia	state	country:Syria:163843	1	1022	2019-06-30 19:05:07.566644	\N	\N	100
state:Tashigang:1337292	S2	Tashigang	state	region:Eastern:	1	501	2019-07-01 13:09:16.897824	\N	\N	100
state:Khulna:1337210	S2	Khulna	state	country:Bangladesh:1210997	1	3043	2019-07-01 13:08:24.336981	\N	\N	100
bay:BaiaDeMaputo:1040651	S2	Baia de Maputo	bay	root	1	1017	2019-06-30 21:40:23.363917	\N	\N	100
state:Siguiri:2415702	S2	Siguiri	state	region:Kankan:8335087	1	3008	2019-06-30 22:29:10.046569	\N	\N	100
channel:Dardanelles:749780	S2	Dardanelles	channel	root	1	1029	2019-06-30 18:43:18.639277	\N	\N	100
state:Polonnaruva:1229899	S2	Pŏḷŏnnaruva	state	region:UturumaĕDaPalata:	1	1700	2019-07-02 12:28:51.988359	\N	\N	100
state:Lhuntshi:1337284	S2	Lhuntshi	state	region:Eastern:	1	509	2019-07-01 13:00:26.486532	\N	\N	100
state:PortLouis:934153	S2	Port Louis	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.755774	\N	\N	100
state:Bumthang:1337278	S2	Bumthang	state	region:Southern:	1	590	2019-07-01 13:00:26.48726	\N	\N	100
region:Southern:	S2	Southern	region	country:Bhutan:1252634	0	1019	2019-07-01 13:00:26.48793	\N	\N	100
state:SamutSongkhram:1606585	S2	Samut Songkhram	state	region:Central:10177180	1	492	2019-07-01 14:50:54.193519	\N	\N	100
state:Southern:933043	S2	Southern	state	country:Botswana:933860	1	6414	2019-06-30 20:18:19.941762	\N	\N	100
state:GrandPort:934466	S2	Grand Port	state	country:Mauritius:934292	1	486	2019-07-03 13:42:31.757994	\N	\N	100
state:Dolj:679134	S2	Dolj	state	country:Romania:798549	1	2062	2019-07-03 17:00:47.528378	\N	\N	100
state:BrodskoPosavska:3337512	S2	Brodsko-Posavska	state	country:Croatia:3202326	1	2576	2019-07-02 17:49:02.615333	\N	\N	100
state:Halland:2708794	S2	Halland	state	country:Sweden:2661886	1	3558	2019-06-30 09:52:00.546444	\N	\N	100
state:ArchipelDesCrozet:936339	S2	Archipel des Crozet	state	country:FrenchSouthernAndAntarcticLands:1546748	1	1957	2019-06-30 18:11:17.25649	\N	\N	100
gulf:GolfoSanMatias:3831702	S2	Golfo San Matías	gulf	root	1	3059	2019-06-30 10:14:53.487448	\N	\N	100
state:Trang:1150006	S2	Trang	state	region:Southern:10234924	1	164	2019-07-01 14:51:34.501142	\N	\N	100
state:Zlatiborski:7581912	S2	Zlatiborski	state	country:Serbia:6290252	1	2561	2019-07-01 18:52:42.496281	\N	\N	100
state:Meuse:2994106	S2	Meuse	state	region:Lorraine:11071622	1	1538	2019-06-30 21:05:31.558165	\N	\N	100
state:Junin:3937485	S2	Junín	state	country:Peru:3932488	1	4474	2019-07-01 02:16:46.280385	\N	\N	100
state:Eschen:3042069	S2	Eschen	state	country:Liechtenstein:3042058	1	506	2019-07-02 01:26:58.301706	\N	\N	100
state:LouangNamtha:1655560	S2	Louang Namtha	state	country:Laos:1655842	1	2104	2019-07-01 13:35:42.154325	\N	\N	100
state:Phatthalung:1607778	S2	Phatthalung	state	region:Southern:10234924	1	496	2019-07-01 14:39:38.887464	\N	\N	100
state:Rezeknes:456197	S2	Rezeknes	state	region:Latgale:7639662	1	1896	2019-06-30 10:10:32.188269	\N	\N	100
state:Balzers:3042074	S2	Balzers	state	country:Liechtenstein:3042058	1	503	2019-07-02 01:26:58.302611	\N	\N	100
state:Catamarca:3862286	S2	Catamarca	state	country:Argentina:3865483	1	12668	2019-07-01 01:21:54.356109	\N	\N	100
state:Paktya:1131257	S2	Paktya	state	country:Afghanistan:1149361	1	762	2019-07-01 15:17:39.508301	\N	\N	100
state:Idlib:169387	S2	Idlib	state	country:Syria:163843	1	1024	2019-06-30 19:11:24.746377	\N	\N	100
state:Flacq:934522	S2	Flacq	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.758862	\N	\N	100
state:GashBarka:448500	S2	Gash Barka	state	country:Eritrea:338010	1	5614	2019-07-01 16:13:54.031585	\N	\N	100
state:BrodskoPosavska:3337523	S2	Brodsko-Posavska	state	country:Croatia:3202326	1	2058	2019-07-02 17:55:30.725497	\N	\N	100
state:Chachoengsao:1611438	S2	Chachoengsao	state	region:Eastern:7114724	1	497	2019-07-01 14:43:38.731067	\N	\N	100
state:Tbilisi:611716	S2	Tbilisi	state	country:Georgia:614540	1	989	2019-07-01 16:19:47.498622	\N	\N	100
state:Orenburg:515001	S2	Orenburg	state	region:Volga:11961325	1	24780	2019-06-30 20:02:39.912865	\N	\N	100
state:ChonBuri:1611108	S2	Chon Buri	state	region:Eastern:7114724	1	490	2019-07-01 14:27:33.765963	\N	\N	100
state:GornoAltay:1506272	S2	Gorno-Altay	state	region:Siberian:11961345	1	15427	2019-07-01 15:55:30.907606	\N	\N	100
state:SouthEast:933044	S2	South-East	state	country:Botswana:933860	1	997	2019-07-02 17:32:09.387669	\N	\N	100
state:Anseba:448501	S2	Anseba	state	country:Eritrea:338010	1	1519	2019-07-01 16:19:47.334469	\N	\N	100
state:Alytaus:864389	S2	Alytaus	state	country:Lithuania:597427	1	2043	2019-07-01 18:36:15.382229	\N	\N	100
state:Veraguas:3700159	S2	Veraguas	state	country:Panama:3703430	1	1854	2019-07-03 00:01:33.853407	\N	\N	100
state:NakhonSiThammarat:1608525	S2	Nakhon Si Thammarat	state	region:Southern:10234924	1	666	2019-07-01 14:46:09.762471	\N	\N	100
state:SlovenskaBistrica:3190533	S2	Slovenska Bistrica	state	region:Podravska:	1	766	2019-06-30 18:07:32.986129	\N	\N	100
state:RiviereNoire:934718	S2	Rivière Noire	state	country:Mauritius:934292	1	486	2019-07-03 13:42:31.759296	\N	\N	100
state:SanJoseDeOcoa:6201372	S2	San José de Ocoa	state	country:DominicanRepublic:3508796	1	1021	2019-06-30 23:42:24.681274	\N	\N	100
state:Rakhine:1298852	S2	Rakhine	state	country:Myanmar:1327865	1	4158	2019-06-30 16:59:16.461492	\N	\N	100
country:Mauritius:934292	S2	Mauritius	country	continent:SevenSeas(OpenOcean):	0	486	2019-07-03 13:42:31.759715	\N	\N	100
region:Podravska:	S2	Podravska	region	country:Slovenia:3190538	0	766	2019-06-30 18:07:32.986541	\N	\N	100
state:HodhEchChargui:2379025	S2	Hodh ech Chargui	state	country:Mauritania:2378080	1	19845	2019-06-30 21:51:17.153178	\N	\N	100
state:Shefa:2208267	S2	Shefa	state	country:Vanuatu:2134431	1	2998	2019-07-01 08:32:48.171848	\N	\N	100
state:Niger:2328925	S2	Niger	state	country:Nigeria:2328926	1	9395	2019-06-30 17:44:14.040976	\N	\N	100
gulf:GolfoCorcovado:3893591	S2	Golfo Corcovado	gulf	root	1	5687	2019-07-01 01:37:49.168285	\N	\N	100
state:Igdir:443184	S2	Iğdir	state	country:Turkey:298795	1	1984	2019-07-01 16:19:50.461798	\N	\N	100
state:Namayingo:8051532	S2	Namayingo	state	region:Eastern:8260673	1	1026	2019-07-01 16:59:34.305408	\N	\N	100
state:Uruzgan:6957553	S2	Uruzgan	state	country:Afghanistan:1149361	1	2512	2019-07-01 15:25:03.327131	\N	\N	100
channel:MozambiqueChannel:923266	S2	Mozambique Channel	channel	root	1	139668	2019-06-30 15:31:50.732407	\N	\N	100
state:Mara:154775	S2	Mara	state	country:Tanzania:149590	1	3091	2019-07-01 16:28:15.688387	\N	\N	100
state:Amudat:235191	S2	Amudat	state	region:Northern:8260674	1	670	2019-07-01 17:11:46.712428	\N	\N	100
state:Mafeteng:932615	S2	Mafeteng	state	country:Lesotho:932692	1	1038	2019-07-01 17:19:18.308511	\N	\N	100
state:Tovuz:584861	S2	Tovuz	state	region:GanjaGazakhEconomicRegion:	1	494	2019-07-01 16:28:57.248039	\N	\N	100
state:Ingush:569665	S2	Ingush	state	region:Volga:11961325	1	7	2019-07-01 15:41:57.451239	\N	\N	100
state:Maekel:448498	S2	Maekel	state	country:Eritrea:338010	1	1023	2019-07-01 16:19:47.335814	\N	\N	100
state:MayoKebbiEst:2428132	S2	Mayo-Kebbi Est	state	country:Chad:2434508	1	3054	2019-06-30 18:09:46.288782	\N	\N	100
state:Gədəbəy:627535	S2	Gədəbəy	state	region:GanjaGazakhEconomicRegion:	1	495	2019-07-01 16:41:15.338742	\N	\N	100
state:WhiteNile:408653	S2	White Nile	state	region:BlueNile:408654	1	5642	2019-06-30 16:06:01.511646	\N	\N	100
state:Arbil:95445	S2	Arbil	state	region:Kurdistan:8429352	1	1005	2019-07-01 16:34:10.878695	\N	\N	100
state:VayotsDzor:409315	S2	Vayots Dzor	state	country:Armenia:174982	1	994	2019-07-01 16:37:42.016811	\N	\N	100
state:ButhaButhe:932888	S2	Butha-Buthe	state	country:Lesotho:932692	1	509	2019-07-01 16:37:43.109833	\N	\N	100
state:Sərur:146858	S2	Şərur	state	region:NaxcivanAutonomousRepublic:	1	991	2019-07-01 16:19:50.461339	\N	\N	100
state:Kaafu:1282208	S2	Kaafu	state	region:NorthCentral:8030594	1	1007	2019-07-01 17:12:53.714003	\N	\N	100
state:Aruba:3577280	S2	Aruba	state	country:Aruba:3577280	1	521	2019-07-01 01:13:45.397909	\N	\N	100
state:Tərtər:584871	S2	Tərtər	state	region:YukhariGarabakhEconomicRegion:	1	988	2019-07-01 16:19:49.645789	\N	\N	100
state:Zaqatala:584604	S2	Zaqatala	state	region:ShakiZaqatalaEconomicRegion:	1	990	2019-07-01 16:38:25.661721	\N	\N	100
state:LordHoweIsland:2155400	S2	Lord Howe Island	state	country:Australia:2077456	1	278	2022-06-09 03:22:48.32041	\N	\N	100
state:Njombe:8469241	S2	Njombe	state	country:Tanzania:149590	1	2787	2019-07-01 17:03:35.341126	\N	\N	100
bay:BaiaDeMarajo:3395454	S2	Baía de Marajó	bay	root	1	1184	2019-06-30 22:26:41.405473	\N	\N	100
state:Raa:1281918	S2	Raa	state	region:North:8030595	1	536	2019-07-01 17:15:36.921263	\N	\N	100
region:Central:931597	S2	Central	region	country:Malawi:927384	0	4146	2019-07-01 17:03:32.741027	\N	\N	100
state:Bouenza:2260668	S2	Bouenza	state	country:Congo:2260494	1	16	2022-07-29 16:47:24.248143	\N	\N	100
state:CoteDOr:3023423	S2	Côte-d'Or	state	region:Bourgogne:11071619	1	1038	2019-06-30 21:10:57.983598	\N	\N	100
river:YangtzeRiver:1815696	S2	Yangtze River	river	root	1	2072	2019-06-30 11:09:11.623587	\N	\N	100
state:Serere:8658743	S2	Serere	state	region:Eastern:8260673	1	503	2019-07-04 14:36:35.413517	\N	\N	100
state:Budaka:8644159	S2	Budaka	state	region:Eastern:8260673	1	512	2019-07-01 16:59:06.759077	\N	\N	100
state:Boeny:7670849	S2	Boeny	state	country:Madagascar:1062947	1	3435	2019-07-01 18:05:51.697855	\N	\N	100
state:Central:400742	S2	Central	state	country:Kenya:192950	1	1615	2019-07-01 17:04:01.207606	\N	\N	100
state:Baa:1282478	S2	Baa	state	region:North:8030595	1	501	2019-07-01 17:19:22.020393	\N	\N	100
state:Kakheti:865537	S2	Kakheti	state	country:Georgia:614540	1	744	2019-07-01 16:28:57.246842	\N	\N	100
state:Fejer:3053028	S2	Fejér	state	region:CentralTransdanubia:	1	2071	2019-07-02 17:55:35.517341	\N	\N	100
state:Susa:409419	S2	Şuşa	state	region:YukhariGarabakhEconomicRegion:	1	498	2019-07-01 16:29:30.857561	\N	\N	100
state:Chittagong:1337200	S2	Chittagong	state	country:Bangladesh:1210997	1	5608	2019-07-01 13:04:35.053303	\N	\N	100
state:Male:1337624	S2	Malé	state	region:Male:1337624	1	508	2019-07-01 17:12:53.714622	\N	\N	100
region:Male:1337624	S2	Malé	region	country:Maldives:1282028	0	507	2019-07-01 17:12:53.715195	\N	\N	100
state:Hakkari:312888	S2	Hakkari	state	country:Turkey:298795	1	993	2019-07-01 16:37:42.281274	\N	\N	100
state:Tororo:226110	S2	Tororo	state	region:Eastern:8260673	1	968	2019-07-01 17:03:32.757858	\N	\N	100
state:LimaProvince:3936451	S2	Lima Province	state	country:Peru:3932488	1	1597	2019-07-01 02:16:39.532518	\N	\N	100
state:Western:400743	S2	Western	state	country:Kenya:192950	1	676	2019-07-01 17:03:32.759224	\N	\N	100
state:Babək:148413	S2	Babək	state	region:NaxcivanAutonomousRepublic:	1	497	2019-07-01 16:37:42.018754	\N	\N	100
state:Nyanza:182763	S2	Nyanza	state	country:Kenya:192950	1	1540	2019-07-01 16:57:13.402774	\N	\N	100
state:Balakən:587010	S2	Balakən	state	region:ShakiZaqatalaEconomicRegion:	1	162	2019-07-01 16:33:14.390134	\N	\N	100
state:AlifuDhaalu:10346475	S2	Alifu Dhaalu	state	region:NorthCentral:8030594	1	1002	2019-07-01 17:32:05.503832	\N	\N	100
state:Budva:3203106	S2	Budva	state	country:Montenegro:3194884	1	1020	2019-07-01 18:30:28.941813	\N	\N	100
state:Meemu:1281985	S2	Meemu	state	region:Central:8030594	1	502	2019-07-01 17:28:14.663784	\N	\N	100
state:Armavir:828260	S2	Armavir	state	country:Armenia:174982	1	1981	2019-07-01 16:19:50.463178	\N	\N	100
state:Mouhili:921780	S2	Moûhîlî	state	country:Comoros:921929	1	1034	2019-07-01 18:17:01.616295	\N	\N	100
state:Bukedea:8657971	S2	Bukedea	state	region:Eastern:8260673	1	513	2019-07-01 17:10:34.091152	\N	\N	100
state:Friesland:2755812	S2	Friesland	state	country:Netherlands:2750405	1	3060	2019-06-30 21:10:25.480777	\N	\N	100
state:Nitriansky:3343956	S2	Nitriansky	state	country:Slovakia:3057568	1	2071	2019-07-02 17:55:35.953358	\N	\N	100
state:Omusati:3371206	S2	Omusati	state	country:Namibia:3355338	1	3073	2019-07-02 18:12:57.270339	\N	\N	100
country:Aruba:3577280	S2	Aruba	country	continent:NorthAmerica:6255149	0	521	2019-07-01 01:13:45.398511	\N	\N	100
state:GaafuAlifu:1282329	S2	Gaafu Alifu	state	region:SouthCentral:8030593	1	327	2019-07-01 17:31:34.634408	\N	\N	100
state:GaafuDhaalu:1282328	S2	Gaafu Dhaalu	state	region:SouthCentral:8030593	1	505	2019-07-01 17:14:03.07344	\N	\N	100
state:Kocani:863865	S2	Kočani	state	region:Eastern:9166195	1	412	2019-07-01 18:31:00.861969	\N	\N	100
state:Dhaalu:1282293	S2	Dhaalu	state	region:Central:8030594	1	522	2019-07-01 17:32:05.504322	\N	\N	100
state:Quthing:932184	S2	Quthing	state	country:Lesotho:932692	1	1040	2019-07-01 17:07:35.304649	\N	\N	100
state:MohaleSHoek:932439	S2	Mohale's Hoek	state	country:Lesotho:932692	1	1037	2019-07-01 17:07:35.304089	\N	\N	100
state:Addu:1281893	S2	Addu	state	region:South:8030589	1	501	2019-07-01 17:30:03.03242	\N	\N	100
state:Oshana:3371207	S2	Oshana	state	country:Namibia:3355338	1	1010	2019-07-04 17:46:26.809438	\N	\N	100
country:Maldives:1282028	S2	Maldives	country	continent:SevenSeas(OpenOcean):	0	507	2019-07-01 17:12:53.715768	\N	\N	100
state:Moroto:229112	S2	Moroto	state	region:Northern:8260674	1	516	2019-07-01 17:12:25.380797	\N	\N	100
country:Namibia:3355338	S2	Namibia	country	continent:Africa:6255146	0	85498	2019-06-30 09:51:38.328841	\N	\N	100
state:Simiyu:8469238	S2	Simiyu	state	country:Tanzania:149590	1	2152	2019-07-01 17:02:02.996043	\N	\N	100
state:BritishIndianOceanTerritory:1282588	S2	British Indian Ocean Territory	state	country:BritishIndianOceanTerritory:1282588	1	1991	2019-07-01 17:15:08.086239	\N	\N	100
state:MaineEtLoire:2996663	S2	Maine-et-Loire	state	region:PaysDeLaLoire:2988289	1	1292	2019-07-01 19:35:52.996852	\N	\N	100
state:Laamu:1282101	S2	Laamu	state	region:UpperSouth:8030590	1	496	2019-07-01 17:33:38.235068	\N	\N	100
region:Basilicata:3182306	S2	Basilicata	region	country:Italy:3175395	0	2775	2019-07-02 17:50:02.889528	\N	\N	100
region:UpperSouth:8030590	S2	Upper South	region	country:Maldives:1282028	0	507	2019-07-01 17:33:38.235582	\N	\N	100
state:Namibe:3347016	S2	Namibe	state	country:Angola:3351879	1	9787	2019-06-30 18:21:52.39711	\N	\N	100
state:Lekoumou:2258534	S2	Lékoumou	state	country:Congo:203312	1	2	2022-07-29 16:47:58.302433	\N	\N	100
state:NordOuest:2223602	S2	Nord-Ouest	state	country:Cameroon:2233387	1	3066	2019-07-01 18:52:03.938429	\N	\N	100
state:Borno:2346794	S2	Borno	state	country:Nigeria:2328926	1	8721	2019-07-01 18:48:42.777387	\N	\N	100
region:South:8030589	S2	South	region	country:Maldives:1282028	0	501	2019-07-01 17:30:03.032911	\N	\N	100
region:Kurzeme:460496	S2	Kurzeme	region	country:Latvia:458258	0	3100	2019-06-30 18:17:43.755669	\N	\N	100
state:Jihocesky:3339537	S2	Jihočeský	state	country:CzechRepublic:3077311	1	2052	2019-06-30 18:19:48.864467	\N	\N	100
state:Melaky:7670852	S2	Melaky	state	country:Madagascar:1062947	1	5453	2019-07-01 18:20:22.17682	\N	\N	100
state:Cabinda:2243266	S2	Cabinda	state	country:Angola:3351879	1	2647	2019-06-30 18:02:17.089108	\N	\N	100
state:Pastaza:3653392	S2	Pastaza	state	country:Ecuador:3658394	1	4742	2019-07-02 08:33:58.93088	\N	\N	100
state:Dhaka:1337179	S2	Dhaka	state	country:Bangladesh:1210997	1	6610	2019-07-01 13:04:35.802755	\N	\N	100
state:Kouroussa:2418435	S2	Kouroussa	state	region:Kankan:8335087	1	987	2019-07-03 20:34:59.876721	\N	\N	100
state:Estuaire:2400682	S2	Estuaire	state	country:Gabon:2400553	1	1587	2019-07-01 18:47:48.547376	\N	\N	100
state:Sabha:2212774	S2	Sabha	state	country:Libya:2215636	1	4110	2019-07-01 18:32:55.489718	\N	\N	100
state:Temburong:1820106	S2	Temburong	state	country:Brunei:1820814	1	2035	2019-07-03 10:30:02.811567	\N	\N	100
state:Kostroma:543871	S2	Kostroma	state	region:Central:11961322	1	12021	2019-06-30 16:58:19.623268	\N	\N	100
state:Surt:2210553	S2	Surt	state	country:Libya:2215636	1	7781	2019-07-01 18:27:47.637672	\N	\N	100
state:Kairouan:2473451	S2	Kairouan	state	country:Tunisia:2464461	1	1547	2019-06-30 18:23:22.810973	\N	\N	100
state:Durham:2650629	S2	Durham	state	region:NorthEast:11591950	1	1010	2019-07-01 19:46:21.3306	\N	\N	100
state:Manyara:435764	S2	Manyara	state	country:Tanzania:149590	1	5203	2019-06-30 20:46:31.913013	\N	\N	100
state:Ogre:457061	S2	Ogre	state	region:Riga:456173	1	501	2019-07-01 18:48:04.390629	\N	\N	100
state:SamtskheJavakheti:865544	S2	Samtskhe-Javakheti	state	country:Georgia:614540	1	2006	2019-07-01 16:29:55.35181	\N	\N	100
state:Belait:1820869	S2	Belait	state	country:Brunei:1820814	1	1986	2019-07-03 10:30:02.812166	\N	\N	100
state:Lampung:1638535	S2	Lampung	state	country:Indonesia:1643084	1	3989	2019-07-02 12:19:42.974141	\N	\N	100
state:Kemo:2385836	S2	Kémo	state	country:CentralAfricanRepublic:239880	1	1606	2019-07-02 16:13:03.628176	\N	\N	100
state:Bishkek:1528334	S2	Bishkek	state	country:Kyrgyzstan:1527747	1	994	2019-06-30 16:42:08.510511	\N	\N	100
state:Kardzhali:864553	S2	Kardzhali	state	country:Bulgaria:732800	1	1031	2019-06-30 19:00:36.569815	\N	\N	100
state:Ouest:2222934	S2	Ouest	state	country:Cameroon:2233387	1	1545	2019-07-01 18:52:03.937944	\N	\N	100
state:NoordHolland:2749879	S2	Noord-Holland	state	country:Netherlands:2750405	1	1022	2019-06-30 21:26:44.638399	\N	\N	100
state:Sachsen:2842566	S2	Sachsen	state	country:Germany:2921044	1	4625	2019-06-30 18:23:02.144229	\N	\N	100
state:SanghaMbaere:2383204	S2	Sangha-Mbaéré	state	country:CentralAfricanRepublic:239880	1	2598	2019-06-30 18:39:33.069041	\N	\N	100
state:Northern:2404798	S2	Northern	state	country:SierraLeone:2403846	1	4546	2019-07-01 20:56:13.383599	\N	\N	100
country:SierraLeone:2403846	S2	Sierra Leone	country	continent:Africa:6255146	0	7549	2019-07-01 20:56:13.384116	\N	\N	100
state:ShidaKartli:865540	S2	Shida Kartli	state	country:Georgia:614540	1	2005	2019-07-01 16:19:47.499305	\N	\N	100
state:Mwanza:152219	S2	Mwanza	state	country:Tanzania:149590	1	4301	2019-07-01 17:08:45.210269	\N	\N	100
year:2022	S2	2022	year	root	1	4124913	2022-01-01 01:49:39.771885	\N	\N	100
state:Hanover:3490145	S2	Hanover	state	country:Jamaica:3489940	1	1509	2019-06-30 10:16:14.250016	\N	\N	100
state:Cundinamarca:3685413	S2	Cundinamarca	state	country:Colombia:3686110	1	2294	2019-07-01 01:16:44.609977	\N	\N	100
state:Aydin:322819	S2	Aydin	state	country:Turkey:298795	1	2093	2019-07-02 16:42:11.162779	\N	\N	100
state:ArunachalPradesh:1278341	S2	Arunachal Pradesh	state	region:Northeast:10575550	1	9464	2019-06-30 17:04:39.753644	\N	\N	100
state:Bayelsa:2595344	S2	Bayelsa	state	country:Nigeria:2328926	1	1032	2019-07-02 18:34:35.025845	\N	\N	100
state:KinshasaCity:2314300	S2	Kinshasa City	state	country:Congo:2260494	1	1207	2019-07-02 18:02:31.022422	\N	\N	100
state:Mayotte:1024031	S2	Mayotte	state	region:Mayotte:	1	1054	2019-07-01 18:28:57.718792	\N	\N	100
state:Kayes:2455517	S2	Kayes	state	country:Mali:2453866	1	14441	2019-06-30 23:00:33.276638	\N	\N	100
state:Monmouthshire:3333244	S2	Monmouthshire	state	region:WestWalesAndTheValleys:	1	1112	2019-07-01 19:13:31.88402	\N	\N	100
state:FreeState:967573	S2	Free State	state	country:SouthAfrica:953987	1	14763	2019-07-01 16:28:02.888183	\N	\N	100
state:JawaTimur:1642668	S2	Jawa Timur	state	country:Indonesia:1643084	1	6435	2019-06-30 11:54:53.809948	\N	\N	100
state:Pirotski:7581803	S2	Pirotski	state	country:Serbia:6290252	1	1713	2019-07-01 18:53:09.897766	\N	\N	100
state:Mehedinti:673612	S2	Mehedinti	state	country:Romania:798549	1	1034	2019-07-01 19:12:55.214372	\N	\N	100
state:OgooueMaritime:2396924	S2	Ogooué-Maritime	state	country:Gabon:2400553	1	2096	2019-07-01 18:53:01.523573	\N	\N	100
state:Tarlac:1682811	S2	Tarlac	state	region:CentralLuzon(RegionIii):	1	1026	2019-06-30 11:09:31.365118	\N	\N	100
state:SanPedroDeMacoris:3493031	S2	San Pedro de Macorís	state	country:DominicanRepublic:3508796	1	521	2019-06-30 23:44:43.899373	\N	\N	100
state:Uige:2236566	S2	Uíge	state	country:Angola:3351879	1	5806	2019-06-30 18:08:24.108765	\N	\N	100
state:WeleNzas:2566983	S2	Wele-Nzás	state	country:EquatorialGuinea:2309096	1	541	2019-07-01 18:55:47.369835	\N	\N	100
state:Nyanga:2397141	S2	Nyanga	state	country:Gabon:2400553	1	2610	2019-07-01 18:58:21.058473	\N	\N	100
state:Andjouan:922001	S2	Andjouân	state	country:Comoros:921929	1	528	2019-07-01 19:02:31.101848	\N	\N	100
state:Norfolk:2641455	S2	Norfolk	state	region:East:2637438	1	2042	2019-07-01 19:34:57.070307	\N	\N	100
state:Nsanje:924567	S2	Nsanje	state	region:Southern:923817	1	1020	2019-06-30 20:03:18.52313	\N	\N	100
state:Henan:1808520	S2	Henan	state	region:SouthCentralChina:	1	20460	2019-06-30 12:31:58.905488	\N	\N	100
state:Rozaje:786233	S2	Rožaje	state	country:Montenegro:3194884	1	1016	2019-07-01 19:00:05.181668	\N	\N	100
state:Sibiu:667267	S2	Sibiu	state	country:Romania:798549	1	2040	2019-07-01 19:00:48.736528	\N	\N	100
state:NorthKhorasan:6201376	S2	North Khorasan	state	country:Iran:130758	1	3515	2019-07-01 18:09:16.345986	\N	\N	100
state:Mon:1308528	S2	Mon	state	country:Myanmar:1327865	1	1612	2019-06-30 10:15:14.139922	\N	\N	100
state:Matale:1235854	S2	Mātale	state	region:MadhyamaPalata:	1	1024	2019-06-30 15:37:28.445844	\N	\N	100
state:Toledo:2510407	S2	Toledo	state	region:CastillaLaMancha:2593111	1	2534	2019-07-01 19:13:26.150012	\N	\N	100
state:EspiritoSanto:3463930	S2	Espírito Santo	state	country:Brazil:3469034	1	8561	2019-06-30 21:50:22.318255	\N	\N	100
state:Lac:2429323	S2	Lac	state	country:Chad:2434508	1	3696	2019-07-01 19:13:48.32361	\N	\N	100
state:Adamawa:2565342	S2	Adamawa	state	country:Nigeria:2328926	1	4172	2019-07-01 18:52:20.976362	\N	\N	100
state:Bulawayo:1105843	S2	Bulawayo	state	country:Zimbabwe:878675	1	496	2019-07-04 14:38:38.31696	\N	\N	100
gulf:KronotskiyGulf	S2	Kronotskiy Gulf	gulf	root	1	2533	2019-07-01 10:10:44.912472	\N	\N	100
state:Drenthe:2756631	S2	Drenthe	state	country:Netherlands:2750405	1	2065	2019-06-30 21:14:45.689258	\N	\N	100
state:Calvados:3029094	S2	Calvados	state	region:BasseNormandie:3034693	1	1903	2019-07-01 19:30:26.930652	\N	\N	100
state:Vendee:2970140	S2	Vendée	state	region:PaysDeLaLoire:2988289	1	2036	2019-07-01 19:44:35.906475	\N	\N	100
state:Negotino:863881	S2	Negotino	state	region:Vardar:833492	1	479	2019-07-01 19:00:59.096356	\N	\N	100
state:Sofala:1026804	S2	Sofala	state	country:Mozambique:1036973	1	8897	2019-06-30 19:49:26.548878	\N	\N	100
state:AbuDhabi:292969	S2	Abu Dhabi	state	country:UnitedArabEmirates:290557	1	9275	2019-07-01 19:19:05.971405	\N	\N	100
state:Cordoba:2519239	S2	Córdoba	state	region:Andalucia:2593109	1	2037	2019-07-01 19:34:36.037879	\N	\N	100
strait:SelatDampier:1645750	S2	Selat Dampier	strait	root	1	1536	2019-06-30 10:10:30.733561	\N	\N	100
state:Madrid:3117732	S2	Madrid	state	region:Madrid:3117732	1	2559	2019-07-01 19:33:04.451043	\N	\N	100
strait:StraitOfGibraltar:2363256	S2	Strait of Gibraltar	strait	root	1	3023	2019-07-01 19:36:34.228248	\N	\N	100
state:Westmoreland:3488081	S2	Westmoreland	state	country:Jamaica:3489940	1	1515	2019-06-30 10:16:14.257504	\N	\N	100
state:RegionMetropolitanaDeSantiago:3873544	S2	Región Metropolitana de Santiago	state	country:Chile:3895114	1	3109	2019-07-01 01:35:01.9176	\N	\N	100
state:CotesDArmor:3023414	S2	Côtes-d'Armor	state	region:Bretagne:3030293	1	439	2019-07-01 19:35:17.571212	\N	\N	100
country:Comoros:921929	S2	Comoros	country	continent:Africa:6255146	0	1546	2019-07-01 19:02:31.102376	\N	\N	100
state:Acklins:3572937	S2	Acklins	state	country:Bahamas:3572887	1	1477	2019-07-02 08:27:11.792619	\N	\N	100
region:Madrid:3117732	S2	Madrid	region	country:Spain:2510769	0	2559	2019-07-01 19:33:04.451539	\N	\N	100
state:Sevilla:2510910	S2	Sevilla	state	region:Andalucia:2593109	1	1540	2019-07-01 19:34:41.190835	\N	\N	100
state:Ceuta:2519582	S2	Ceuta	state	region:Ceuta:7577100	1	1013	2019-07-01 19:36:34.229226	\N	\N	100
state:Gjirokaster:865733	S2	Gjirokastër	state	country:Albania:783754	1	506	2019-07-01 19:05:36.388534	\N	\N	100
state:Merida:3632306	S2	Mérida	state	country:Venezuela:3625428	1	1072	2019-07-01 01:13:31.176735	\N	\N	100
state:Suffolk:2636561	S2	Suffolk	state	region:East:2637438	1	537	2019-07-01 19:33:27.825874	\N	\N	100
state:Kent:3333158	S2	Kent	state	region:SouthEast:2637438	1	3043	2019-07-01 19:13:33.488845	\N	\N	100
sea:BellingshausenSea:4036628	S2	Bellingshausen Sea	sea	root	1	18615	2019-08-31 20:11:46.024388	\N	\N	100
state:Cantabria:3336898	S2	Cantabria	state	region:Cantabria:3336898	1	1525	2019-07-01 19:32:25.830616	\N	\N	100
state:Gauteng:1085594	S2	Gauteng	state	country:SouthAfrica:953987	1	3255	2019-07-01 17:07:27.003919	\N	\N	100
region:Cantabria:3336898	S2	Cantabria	region	country:Spain:2510769	0	1525	2019-07-01 19:32:25.831078	\N	\N	100
state:Malaga:2514254	S2	Málaga	state	region:Andalucia:2593109	1	1026	2019-07-01 19:36:34.050328	\N	\N	100
state:Lipetsk:535120	S2	Lipetsk	state	region:Central:11961322	1	1541	2019-07-01 19:40:28.971151	\N	\N	100
state:LaRomana:3500955	S2	La Romana	state	country:DominicanRepublic:3508796	1	1043	2019-06-30 23:44:43.897954	\N	\N	100
state:Valladolid:3106671	S2	Valladolid	state	region:CastillaYLeon:3336900	1	510	2019-07-01 19:34:38.266068	\N	\N	100
state:LaAltagracia:3503706	S2	La Altagracia	state	country:DominicanRepublic:3508796	1	526	2019-06-30 23:45:45.250411	\N	\N	100
state:Bheri:1283606	S2	Bheri	state	region:MidWestern:7289706	1	674	2019-07-01 16:16:05.386754	\N	\N	100
state:Dorset:2651079	S2	Dorset	state	region:SouthWest:11591956	1	787	2019-07-01 19:34:22.938004	\N	\N	100
state:LaRioja:3336897	S2	La Rioja	state	region:LaRioja:3336897	1	1394	2019-07-01 19:31:58.653246	\N	\N	100
day:01	S2	01	day	root	1	936644	2019-07-01 08:50:48.080133	\N	\N	100
state:OuterIslands:448411	S2	Outer Islands	state	country:Seychelles:241170	1	523	2019-07-01 20:02:16.397535	\N	\N	100
state:Cambridgeshire:2653940	S2	Cambridgeshire	state	region:East:2637438	1	1017	2019-07-01 19:38:47.782057	\N	\N	100
fjord:Sognefjorden:3137976	S2	Sognefjorden	fjord	root	1	1868	2019-07-01 19:48:00.045505	\N	\N	100
state:Guidimaka:2379216	S2	Guidimaka	state	country:Mauritania:2378080	1	2000	2019-07-01 20:56:28.459879	\N	\N	100
state:Lelouma:2595303	S2	Lélouma	state	region:Labe:8335089	1	503	2019-07-01 20:59:05.316809	\N	\N	100
state:Shinyanga:150004	S2	Shinyanga	state	country:Tanzania:149590	1	4293	2019-07-01 16:39:28.636108	\N	\N	100
state:Southern:2403745	S2	Southern	state	country:SierraLeone:2403846	1	3982	2019-07-01 20:59:55.83239	\N	\N	100
state:Somerset:2637532	S2	Somerset	state	region:SouthWest:11591956	1	507	2019-07-01 19:53:52.578577	\N	\N	100
state:Pita:2416440	S2	Pita	state	region:Mamou:8335090	1	500	2019-07-01 20:59:05.3179	\N	\N	100
state:Manche:2996268	S2	Manche	state	region:BasseNormandie:3034693	1	2066	2019-07-01 19:50:27.175912	\N	\N	100
state:Boffa:2422967	S2	Boffa	state	region:Boke:8335085	1	1004	2019-07-01 21:06:16.437721	\N	\N	100
state:Mamou:8335090	S2	Mamou	state	region:Mamou:8335090	1	2967	2019-07-01 21:00:07.014092	\N	\N	100
state:Mali:2417885	S2	Mali	state	region:Labe:8335089	1	1505	2019-07-01 21:07:08.417306	\N	\N	100
state:Mudug:53707	S2	Mudug	state	country:Somalia:51537	1	8268	2019-07-01 19:43:23.654405	\N	\N	100
state:Orne:2989247	S2	Orne	state	region:BasseNormandie:3034693	1	1542	2019-07-01 19:57:44.34735	\N	\N	100
state:Dalaba:2422377	S2	Dalaba	state	region:Mamou:8335090	1	998	2019-07-01 21:01:06.552069	\N	\N	100
state:Staffordshire:2637141	S2	Staffordshire	state	region:WestMidlands:11591953	1	513	2019-07-01 19:42:13.75765	\N	\N	100
state:Yalova:862469	S2	Yalova	state	country:Turkey:298795	1	2579	2019-07-02 16:35:24.608868	\N	\N	100
region:Mamou:8335090	S2	Mamou	region	country:Guinea:2420477	0	3979	2019-07-01 20:59:05.318432	\N	\N	100
state:Kindia:8335088	S2	Kindia	state	region:Kindia:8335088	1	504	2019-07-01 21:01:06.553275	\N	\N	100
state:Avila:3129138	S2	Ávila	state	region:CastillaYLeon:3336900	1	1014	2019-07-01 19:52:47.30137	\N	\N	100
state:RabatSaleZemmourZaer:	S2	Rabat - Salé - Zemmour - Zaer	state	country:Morocco:2542007	1	2303	2019-07-01 20:56:27.43056	\N	\N	100
state:Yvelines:2967196	S2	Yvelines	state	region:IleDeFrance:3012874	1	1520	2019-06-30 21:13:54.128825	\N	\N	100
country:Bangladesh:1210997	S2	Bangladesh	country	continent:Asia:6255147	0	17705	2019-07-01 13:04:08.057064	\N	\N	100
state:Conakry:2420477	S2	Conakry	state	region:Conakry:2420477	1	499	2019-07-01 21:13:48.144111	\N	\N	100
state:Nugaal:53477	S2	Nugaal	state	country:Somalia:51537	1	3598	2019-07-01 19:46:04.833563	\N	\N	100
state:HadjerLamis:7603251	S2	Hadjer-Lamis	state	country:Chad:2434508	1	4085	2019-06-30 17:11:06.12867	\N	\N	100
state:Bizkaia:3104469	S2	Bizkaia	state	region:PaisVasco:3336903	1	1033	2019-07-01 19:46:07.000697	\N	\N	100
state:Menabe:7670902	S2	Menabe	state	country:Madagascar:1062947	1	7340	2019-07-01 20:15:19.344773	\N	\N	100
state:CuvetteOuest:2593118	S2	Cuvette-Ouest	state	country:Congo:2260494	1	1703	2019-06-30 18:41:38.439341	\N	\N	100
state:Katavi:8469240	S2	Katavi	state	country:Tanzania:149590	1	8138	2019-07-01 17:04:57.696495	\N	\N	100
state:Tulcea:664517	S2	Tulcea	state	country:Romania:798549	1	1601	2019-06-30 19:02:37.147099	\N	\N	100
gulf:GulfOfMannar:1281787	S2	Gulf of Mannar	gulf	root	1	6295	2019-06-30 15:19:30.591675	\N	\N	100
state:Western:2403068	S2	Western	state	country:SierraLeone:2403846	1	996	2019-07-01 21:12:48.320594	\N	\N	100
state:Falcon:3640873	S2	Falcón	state	country:Venezuela:3625428	1	2659	2019-07-01 01:12:08.48045	\N	\N	100
state:Segovia:3109254	S2	Segovia	state	region:CastillaYLeon:3336900	1	1530	2019-07-01 19:43:50.894429	\N	\N	100
region:Conakry:2420477	S2	Conakry	region	country:Guinea:2420477	0	499	2019-07-01 21:13:48.144602	\N	\N	100
state:Gibraltar:2411586	S2	Gibraltar	state	country:Gibraltar:2411586	1	1014	2019-07-01 19:48:01.155893	\N	\N	100
state:Zinguldak:737021	S2	Zinguldak	state	country:Turkey:298795	1	2048	2019-07-01 22:20:44.995706	\N	\N	100
state:Bagmati:1283710	S2	Bagmati	state	region:Central:7289707	1	2074	2019-06-30 15:43:18.021927	\N	\N	100
state:AtsimoAndrefana:7670913	S2	Atsimo-Andrefana	state	country:Madagascar:1062947	1	6006	2019-07-01 20:29:20.686144	\N	\N	100
state:StingaNistrului:	S2	Stîngă Nistrului	state	country:Moldova:617790	1	1042	2019-06-30 18:39:36.849217	\N	\N	100
state:Fukui:1863983	S2	Fukui	state	region:Chubu:1864496	1	1532	2019-06-30 10:33:27.502699	\N	\N	100
state:Galla:1246292	S2	Gālla	state	region:DakunuPalata:	1	1538	2019-06-30 15:18:53.222151	\N	\N	100
state:LaGuajira:3678847	S2	La Guajira	state	country:Colombia:3686110	1	3637	2019-07-01 01:17:33.479353	\N	\N	100
country:Seychelles:241170	S2	Seychelles	country	continent:SevenSeas(OpenOcean):	0	1141	2019-07-01 20:02:16.398124	\N	\N	100
country:Gibraltar:2411586	S2	Gibraltar	country	continent:Europe:6255148	0	1014	2019-07-01 19:48:01.156627	\N	\N	100
state:SanMartin:3692385	S2	San Martín	state	country:Peru:3932488	1	7775	2019-07-01 00:53:10.001826	\N	\N	100
state:Assaba:2381344	S2	Assaba	state	country:Mauritania:2378080	1	4052	2019-07-01 20:53:01.360313	\N	\N	100
state:Sark:6696201	S2	Sark	state	country:Guernsey:3042362	1	1015	2019-07-01 19:56:17.070568	\N	\N	100
state:ChaouiaOuardigha:2597548	S2	Chaouia - Ouardigha	state	country:Morocco:2542007	1	2085	2019-07-01 20:56:45.43848	\N	\N	100
state:Jersey:3042142	S2	Jersey	state	country:Jersey:3042142	1	509	2019-07-01 19:56:17.07188	\N	\N	100
country:Jersey:3042142	S2	Jersey	country	continent:Europe:6255148	0	509	2019-07-01 19:56:17.072433	\N	\N	100
state:TadlaAzilal:2597555	S2	Tadla - Azilal	state	country:Morocco:2542007	1	4629	2019-07-01 20:51:49.110046	\N	\N	100
state:Tabora:149653	S2	Tabora	state	country:Tanzania:149590	1	7542	2019-07-01 16:39:28.636687	\N	\N	100
state:Aksaray:443185	S2	Aksaray	state	country:Turkey:298795	1	415	2019-07-01 22:28:35.618515	\N	\N	100
state:Forecariah:2420983	S2	Forécariah	state	region:Kindia:8335088	1	998	2019-07-01 21:13:48.145009	\N	\N	100
state:GrandCasablanca:2553603	S2	Grand Casablanca	state	country:Morocco:2542007	1	529	2019-07-01 21:00:18.814679	\N	\N	100
state:NuvaraEliya:1232781	S2	Nuvara Ĕliya	state	region:MadhyamaPalata:	1	1031	2019-06-30 15:22:56.025663	\N	\N	100
state:Tougue:2414544	S2	Tougué	state	region:Labe:8335089	1	1986	2019-07-01 21:00:07.015013	\N	\N	100
state:Dubreka:2421533	S2	Dubréka	state	region:Kindia:8335088	1	500	2019-07-01 21:06:16.438668	\N	\N	100
state:HamgyongBukto:2044245	S2	Hamgyŏng-bukto	state	country:NorthKorea:1873107	1	2743	2019-07-01 03:38:56.828643	\N	\N	100
state:Zajas:863917	S2	Zajas	state	region:Southwestern:9072896	1	505	2019-07-01 18:37:40.410728	\N	\N	100
state:Sinop:739598	S2	Sinop	state	country:Turkey:298795	1	1524	2019-07-01 23:12:16.317807	\N	\N	100
state:Vrapciste:863914	S2	Vrapcište	state	country:Macedonia:718075	1	505	2019-07-01 18:37:40.41122	\N	\N	100
state:OgooueIvindo:2396926	S2	Ogooué-Ivindo	state	country:Gabon:2400553	1	5085	2019-06-30 18:18:40.022759	\N	\N	100
state:Plateaux:2255422	S2	Plateaux	state	country:Congo:2260494	1	4237	2019-06-30 18:10:42.838071	\N	\N	100
region:MadhyamaPalata:	S2	Madhyama paḷāta	region	country:SriLanka:1227603	0	2077	2019-06-30 15:22:56.026168	\N	\N	100
state:SaintLuke:3575622	S2	Saint Luke	state	country:Dominica:3575830	1	515	2019-07-02 00:12:17.349458	\N	\N	100
state:Koundara:2418595	S2	Koundara	state	region:Boke:8335085	1	504	2019-07-01 21:17:07.500885	\N	\N	100
state:NuevaEsparta:3631462	S2	Nueva Esparta	state	country:Venezuela:3625428	1	1035	2019-07-02 00:17:06.224683	\N	\N	100
state:NorthEast:933210	S2	North-East	state	country:Botswana:933860	1	653	2019-07-04 13:52:41.681731	\N	\N	100
state:Ratnapura:1228729	S2	Ratnapura	state	region:SabaragamuvaPalata:	1	1030	2019-06-30 15:22:56.027529	\N	\N	100
state:GrandAnse:3724613	S2	Grand'Anse	state	country:Haiti:3723988	1	988	2019-07-02 08:36:36.996932	\N	\N	100
region:SabaragamuvaPalata:	S2	Sabaragamuva paḷāta	region	country:SriLanka:1227603	0	1036	2019-06-30 15:22:56.02798	\N	\N	100
state:SaintHelena:6930057	S2	Saint Helena	state	country:SaintHelena:3370751	1	994	2019-07-01 23:34:47.40687	\N	\N	100
state:Kalutara:1241963	S2	Kaḷutara	state	region:BasnahiraPalata:	1	526	2019-06-30 15:22:56.026631	\N	\N	100
year:2017	S2	2017	year	root	1	61544	2019-09-27 14:24:56.66857	\N	\N	100
state:SaintJamesWindward:3575177	S2	Saint James Windward	state	region:Nevis:3575174	1	1033	2019-07-02 00:08:37.550168	\N	\N	100
state:Kars:743942	S2	Kars	state	country:Turkey:298795	1	2477	2019-07-01 16:37:42.966016	\N	\N	100
state:Sucre:3626655	S2	Sucre	state	country:Venezuela:3625428	1	1555	2019-07-02 00:16:47.03726	\N	\N	100
region:Nevis:3575174	S2	Nevis	region	country:SaintKittsAndNevis:3575174	0	1033	2019-07-02 00:08:37.550663	\N	\N	100
state:Savanes:2364205	S2	Savanes	state	country:Togo:2363686	1	2515	2019-07-02 00:18:44.919282	\N	\N	100
state:SaintMark:3579913	S2	Saint Mark	state	country:Grenada:3580239	1	1020	2019-07-02 00:16:17.37942	\N	\N	100
state:Tetovo:863907	S2	Tetovo	state	country:Macedonia:718075	1	505	2019-07-01 18:37:40.411695	\N	\N	100
state:Kompienga:2597260	S2	Kompienga	state	region:Est:6930709	1	2027	2019-07-02 00:53:28.163401	\N	\N	100
state:GreaterAccra:2300569	S2	Greater Accra	state	country:Ghana:2300660	1	2452	2019-07-02 00:58:10.744689	\N	\N	100
state:Tillaberi:2595293	S2	Tillabéri	state	country:Niger:2440476	1	8558	2019-07-02 00:34:28.977414	\N	\N	100
state:Atlantico:3689436	S2	Atlántico	state	country:Colombia:3686110	1	1213	2019-07-02 08:33:12.260271	\N	\N	100
state:SaintDavid:3579932	S2	Saint David	state	country:Grenada:3580239	1	1016	2019-07-02 00:16:17.379996	\N	\N	100
state:SaintPhilip:3576015	S2	Saint Philip	state	country:AntiguaAndBarbuda:3576396	1	515	2019-07-02 00:17:05.904191	\N	\N	100
state:Western:2083549	S2	Western	state	country:PapuaNewGuinea:2088628	1	8640	2019-06-30 20:06:39.113789	\N	\N	100
state:Tapoa:2354771	S2	Tapoa	state	region:Est:6930709	1	1549	2019-07-02 00:27:54.3101	\N	\N	100
state:Paphos:146213	S2	Paphos	state	country:Cyprus:146670	1	1023	2019-07-01 22:51:41.921909	\N	\N	100
state:Duarte:3508718	S2	Duarte	state	country:DominicanRepublic:3508796	1	1535	2019-06-30 23:42:03.569411	\N	\N	100
country:DominicanRepublic:3508796	S2	Dominican Republic	country	continent:NorthAmerica:6255149	0	6284	2019-06-30 23:42:03.570357	\N	\N	100
state:Hambantota:1244925	S2	Hambantŏṭa	state	region:DakunuPalata:	1	1504	2019-07-02 12:38:24.047898	\N	\N	100
state:SaintMark:3575621	S2	Saint Mark	state	country:Dominica:3575830	1	516	2019-07-02 00:12:17.350166	\N	\N	100
state:IlesSaintPaulEtNouvelleAmsterdam:1547221	S2	Iles Saint-Paul et Nouvelle-Amsterdam	state	country:FrenchSouthernAndAntarcticLands:1546748	1	255	2022-06-10 08:01:35.265561	\N	\N	100
state:Worcestershire:2633560	S2	Worcestershire	state	region:WestMidlands:11591953	1	449	2019-07-01 19:51:01.651558	\N	\N	100
bay:WagerBay:6174960	S2	Wager Bay	bay	root	1	3609	2019-07-01 05:23:20.992193	\N	\N	100
state:Schaan:3042042	S2	Schaan	state	country:Liechtenstein:3042058	1	505	2019-07-02 01:26:58.303482	\N	\N	100
state:Lindi:155946	S2	Lindi	state	country:Tanzania:149590	1	7418	2019-06-30 20:13:59.141988	\N	\N	100
state:Gelderland:2755634	S2	Gelderland	state	country:Netherlands:2750405	1	3059	2019-06-30 21:10:51.672267	\N	\N	100
state:SaintPatrick:3579907	S2	Saint Patrick	state	country:Grenada:3580239	1	1020	2019-07-02 00:16:17.380517	\N	\N	100
state:Chechnya:569665	S2	Chechnya	state	region:Volga:11961325	1	1313	2019-07-01 15:47:40.673589	\N	\N	100
state:SaintJohn:3579919	S2	Saint John	state	country:Grenada:3580239	1	1016	2019-07-02 00:16:17.381031	\N	\N	100
state:SaintAndrew:3579938	S2	Saint Andrew	state	country:Grenada:3580239	1	1016	2019-07-02 00:16:17.381512	\N	\N	100
state:SaintDavid:3575630	S2	Saint David	state	country:Dominica:3575830	1	1030	2019-07-02 00:12:17.350849	\N	\N	100
state:Sud:3716952	S2	Sud	state	country:Haiti:3723988	1	987	2019-07-02 08:36:36.997871	\N	\N	100
state:SaintAndrew:3575632	S2	Saint Andrew	state	country:Dominica:3575830	1	517	2019-07-02 00:17:12.50192	\N	\N	100
state:Vorarlberg:2762300	S2	Vorarlberg	state	country:Austria:2782113	1	518	2019-07-02 01:26:58.30482	\N	\N	100
state:AppenzellAusserrhoden:2661739	S2	Appenzell Ausserrhoden	state	country:Switzerland:2658434	1	821	2019-07-02 01:26:58.306672	\N	\N	100
state:Zaporizhzhya:687700	S2	Zaporizhzhya	state	country:Ukraine:690791	1	6674	2019-07-01 22:28:04.841146	\N	\N	100
state:Barbuda:3576390	S2	Barbuda	state	country:AntiguaAndBarbuda:3576396	1	515	2019-07-02 00:13:30.789115	\N	\N	100
state:Martinique:3570311	S2	Martinique	state	region:Martinique:	1	2033	2019-07-02 00:12:17.353888	\N	\N	100
state:SaintPeter:3575618	S2	Saint Peter	state	country:Dominica:3575830	1	515	2019-07-02 00:17:12.498268	\N	\N	100
region:NaĕGenahiraPalata:	S2	Næ̆gĕnahira paḷāta	region	country:SriLanka:1227603	0	2091	2019-06-30 15:21:57.498385	\N	\N	100
region:Northern:924591	S2	Northern	region	country:Malawi:927384	0	5708	2019-07-01 17:04:10.44486	\N	\N	100
state:Central:2302353	S2	Central	state	country:Ghana:2300660	1	1030	2019-07-02 00:52:22.083024	\N	\N	100
region:UvaPalata:	S2	Ūva paḷāta	region	country:SriLanka:1227603	0	1544	2019-07-02 12:26:32.863516	\N	\N	100
state:Boyaca:3688536	S2	Boyacá	state	country:Colombia:3686110	1	3674	2019-07-01 01:14:09.221614	\N	\N	100
state:Naama:7417641	S2	Naâma	state	country:Algeria:2589581	1	4637	2019-06-30 22:53:31.84335	\N	\N	100
state:Rumphi:924100	S2	Rumphi	state	region:Northern:924591	1	2582	2019-07-01 17:04:10.444418	\N	\N	100
state:Goranboy:586112	S2	Goranboy	state	region:GanjaGazakhEconomicRegion:	1	990	2019-07-01 16:19:49.647118	\N	\N	100
state:SaintGeorge:3575628	S2	Saint George	state	country:Dominica:3575830	1	1028	2019-07-02 00:12:17.352118	\N	\N	100
region:Martinique:	S2	Martinique	region	country:France:3017382	0	2033	2019-07-02 00:12:17.354338	\N	\N	100
state:Sassari:3167094	S2	Sassari	state	region:Sardegna:2523228	1	1426	2019-07-02 01:28:33.025273	\N	\N	100
state:Wicklow:2960935	S2	Wicklow	state	region:MidEast:	1	1021	2019-06-30 21:42:11.037492	\N	\N	100
state:Pando:3908600	S2	Pando	state	country:Bolivia:3923057	1	8583	2019-07-02 00:45:10.736423	\N	\N	100
state:MavrovoAndRostusa:863892	S2	Mavrovo and Rostusa	state	region:Polog:786909	1	504	2019-07-01 18:37:40.412244	\N	\N	100
day:08	S2	08	day	root	1	912141	2019-07-08 03:14:09.451709	\N	\N	100
state:SaintPatrick:3575620	S2	Saint Patrick	state	country:Dominica:3575830	1	1028	2019-07-02 00:12:17.352597	\N	\N	100
country:Bhutan:1252634	S2	Bhutan	country	continent:Asia:6255147	0	5055	2019-07-01 13:00:26.488852	\N	\N	100
state:SaintJohn:3575626	S2	Saint John	state	country:Dominica:3575830	1	517	2019-07-02 00:17:12.500297	\N	\N	100
state:Gourma:2360223	S2	Gourma	state	region:Est:6930709	1	1536	2019-07-02 00:40:40.714854	\N	\N	100
state:Shemgang:1337291	S2	Shemgang	state	region:Southern:	1	518	2019-07-01 13:08:45.476828	\N	\N	100
state:Zoundweogo:2353169	S2	Zoundwéogo	state	region:CentreSud:6930708	1	1002	2019-07-02 00:59:45.184115	\N	\N	100
state:UpperEast:2294291	S2	Upper East	state	country:Ghana:2300660	1	1505	2019-07-02 00:19:05.893555	\N	\N	100
state:Koulpelogo:2597261	S2	Koulpélogo	state	region:CentreEst:6930705	1	1006	2019-07-02 00:53:28.162016	\N	\N	100
state:Chitipa:235749	S2	Chitipa	state	country:Malawi:927384	1	3409	2019-07-01 17:04:10.445245	\N	\N	100
region:CentreEst:6930705	S2	Centre-Est	region	country:BurkinaFaso:2361809	0	2019	2019-07-02 00:53:28.162693	\N	\N	100
region:Est:6930709	S2	Est	region	country:BurkinaFaso:2361809	0	6153	2019-07-02 00:27:54.310563	\N	\N	100
country:Malawi:927384	S2	Malawi	country	continent:Africa:6255146	0	14628	2019-06-30 20:01:56.417261	\N	\N	100
state:Nahouri:2357548	S2	Nahouri	state	region:CentreSud:6930708	1	964	2019-07-02 00:18:44.818445	\N	\N	100
state:Katsina:2334797	S2	Katsina	state	country:Nigeria:2328926	1	2079	2019-07-02 18:32:05.645701	\N	\N	100
state:SaintPaul:3576017	S2	Saint Paul	state	country:AntiguaAndBarbuda:3576396	1	516	2019-07-02 00:17:05.904845	\N	\N	100
state:Lucca:3174529	S2	Lucca	state	region:Toscana:3165361	1	1507	2019-07-02 01:24:15.361414	\N	\N	100
state:Komondjari:2597259	S2	Komondjari	state	region:Est:6930709	1	516	2019-07-02 01:09:02.342879	\N	\N	100
state:Boulgou:2362006	S2	Boulgou	state	region:CentreEst:6930705	1	508	2019-07-02 00:59:45.185115	\N	\N	100
state:Blekinge:2721357	S2	Blekinge	state	country:Sweden:2661886	1	1654	2019-07-02 01:41:12.430558	\N	\N	100
state:SaintPeter:3576016	S2	Saint Peter	state	country:AntiguaAndBarbuda:3576396	1	1029	2019-07-02 00:13:30.787795	\N	\N	100
state:Schellenberg:3042038	S2	Schellenberg	state	country:Liechtenstein:3042058	1	506	2019-07-02 01:26:58.299315	\N	\N	100
state:SaintGeorge:3576024	S2	Saint George	state	country:AntiguaAndBarbuda:3576396	1	1029	2019-07-02 00:13:30.788261	\N	\N	100
state:Mauren:3042056	S2	Mauren	state	country:Liechtenstein:3042058	1	506	2019-07-02 01:26:58.299822	\N	\N	100
state:Kouritenga:2358700	S2	Kouritenga	state	region:CentreEst:6930705	1	1003	2019-07-02 00:40:40.714359	\N	\N	100
state:Monaragala:1234818	S2	Mŏṇarāgala	state	region:UvaPalata:	1	519	2019-07-02 12:38:24.049782	\N	\N	100
state:SaintMary:3576018	S2	Saint Mary	state	country:AntiguaAndBarbuda:3576396	1	516	2019-07-02 00:17:05.905531	\N	\N	100
state:Esteli:3619193	S2	Estelí	state	country:Nicaragua:3617476	1	842	2019-07-02 01:43:15.577778	\N	\N	100
country:SaintHelena:3370751	S2	Saint Helena	country	continent:SevenSeas(OpenOcean):	0	2463	2019-07-01 23:34:47.407516	\N	\N	100
state:CorseDuSud:3023514	S2	Corse-du-Sud	state	region:Corse:3023518	1	1020	2019-07-02 01:47:00.809878	\N	\N	100
state:OlbiaTempio:6457401	S2	Olbia-Tempio	state	region:Sardegna:2523228	1	1009	2019-07-02 01:41:03.489973	\N	\N	100
gulf:GolfoDeGuayaquil:3696796	S2	Golfo de Guayaquil	gulf	root	1	2371	2019-06-30 10:16:38.987943	\N	\N	100
region:Sardegna:2523228	S2	Sardegna	region	country:Italy:3175395	0	5461	2019-07-02 01:28:33.025843	\N	\N	100
state:Leon:3618029	S2	León	state	country:Nicaragua:3617476	1	1596	2019-07-02 01:52:11.052492	\N	\N	100
state:Madriz:3617796	S2	Madriz	state	country:Nicaragua:3617476	1	1972	2019-07-02 01:43:15.578314	\N	\N	100
state:SaintJoseph:3575625	S2	Saint Joseph	state	country:Dominica:3575830	1	514	2019-07-02 00:17:12.500815	\N	\N	100
state:Soum:2355248	S2	Soum	state	region:Sahel:6930713	1	1990	2019-07-02 00:39:32.680534	\N	\N	100
state:Chinandega:3620380	S2	Chinandega	state	country:Nicaragua:3617476	1	1435	2019-07-02 01:43:15.578833	\N	\N	100
region:Faranah:8335086	S2	Faranah	region	country:Guinea:2420477	0	5930	2019-06-30 21:58:25.397592	\N	\N	100
state:Trento:3165241	S2	Trento	state	region:TrentinoAltoAdige:3165244	1	1019	2019-07-02 01:56:23.226402	\N	\N	100
state:Kukes:865735	S2	Kukës	state	country:Albania:783754	1	1530	2019-07-01 18:37:40.414964	\N	\N	100
state:Tirol:2763586	S2	Tirol	state	country:Austria:2782113	1	2541	2019-07-02 01:27:46.719899	\N	\N	100
state:Sondrio:3166396	S2	Sondrio	state	region:Lombardia:3174618	1	1000	2019-07-02 01:54:48.998726	\N	\N	100
state:Zaire:2236355	S2	Zaire	state	country:Angola:3351879	1	4699	2019-06-30 18:00:46.357802	\N	\N	100
state:Annobon:2310307	S2	Annobón	state	country:EquatorialGuinea:2309096	1	165	2022-06-13 14:18:41.546997	\N	\N	100
state:CarboniaIglesias:6457400	S2	Carbonia-Iglesias	state	region:Sardegna:2523228	1	501	2019-07-02 01:35:48.348812	\N	\N	100
state:Pavia:3171364	S2	Pavia	state	region:Lombardia:3174618	1	1155	2019-07-02 01:43:38.339596	\N	\N	100
state:Itapua:3437923	S2	Itapúa	state	country:Paraguay:3437598	1	2553	2019-06-30 22:40:16.207465	\N	\N	100
state:Sodermanland:2676207	S2	Södermanland	state	country:Sweden:2661886	1	2092	2019-07-02 01:42:16.566868	\N	\N	100
state:Verona:3164526	S2	Verona	state	region:Veneto:3164604	1	1006	2019-07-02 01:56:23.227825	\N	\N	100
lagoon:StettinerHaff:3083820	S2	Stettiner Haff	lagoon	root	1	1007	2019-07-02 01:56:31.04394	\N	\N	100
state:Amran:6201194	S2	Amran	state	country:Yemen:69543	1	1564	2019-06-30 19:20:45.057198	\N	\N	100
state:Yagha:2597268	S2	Yagha	state	region:Sahel:6930713	1	1524	2019-07-02 00:59:54.234788	\N	\N	100
state:Misiones:3430657	S2	Misiones	state	country:Argentina:3865483	1	3671	2019-06-30 22:32:07.19016	\N	\N	100
state:ReggioEmilia:3169524	S2	Reggio Emilia	state	region:EmiliaRomagna:3177401	1	503	2019-07-02 01:53:17.895045	\N	\N	100
state:Kissidougou:2419470	S2	Kissidougou	state	region:Faranah:8335086	1	1994	2019-06-30 21:58:25.397088	\N	\N	100
state:Kecskemet:3050434	S2	Kecskemét	state	region:GreatSouthernPlain:	1	1	2023-05-24 16:10:58.789793	\N	\N	100
state:Bozen:3181912	S2	Bozen	state	region:TrentinoAltoAdige:3165244	1	2138	2019-07-02 01:44:05.583046	\N	\N	100
state:Geylegphug:1337282	S2	Geylegphug	state	region:Central:	1	1012	2019-07-01 13:08:45.477295	\N	\N	100
state:Jijel:2492910	S2	Jijel	state	country:Algeria:2589581	1	1012	2019-07-02 01:45:50.165345	\N	\N	100
state:Batna:2505569	S2	Batna	state	country:Algeria:2589581	1	1053	2019-07-02 01:38:45.553712	\N	\N	100
state:ManuS:	S2	Manu's	state	country:AmericanSamoa:5880801	1	497	2019-07-02 08:06:26.313129	\N	\N	100
state:Ceara:3402362	S2	Ceará	state	country:Brazil:3469034	1	13560	2019-06-30 22:24:12.725149	\N	\N	100
state:Niue:4036232	S2	Niue	state	country:Niue:4036232	1	994	2019-07-02 08:06:25.584807	\N	\N	100
state:Tigray:444187	S2	Tigray	state	country:Ethiopia:337996	1	5625	2019-07-01 16:19:47.561488	\N	\N	100
gulf:GolfoDePenas:11883358	S2	Golfo de Penas	gulf	root	1	3062	2019-07-01 01:22:18.118908	\N	\N	100
state:Lambayeque:3695753	S2	Lambayeque	state	country:Peru:3932488	1	1531	2019-07-02 08:22:11.184175	\N	\N	100
state:Genova:3176217	S2	Genova	state	region:Liguria:3174725	1	166	2019-07-02 01:53:31.221236	\N	\N	100
state:AtsimoAtsinanana:7670908	S2	Atsimo-Atsinanana	state	country:Madagascar:1062947	1	2089	2019-06-30 15:40:13.147273	\N	\N	100
state:Oristrano:2523962	S2	Oristrano	state	region:Sardegna:2523228	1	1002	2019-07-02 01:45:18.88206	\N	\N	100
state:Nord:2140685	S2	Nord	state	country:NewCaledonia:2139685	1	2571	2019-07-02 08:06:25.629088	\N	\N	100
state:Eastern:5880801	S2	Eastern	state	country:AmericanSamoa:5880801	1	546	2019-07-02 08:11:10.864041	\N	\N	100
state:Bayburt:862471	S2	Bayburt	state	country:Turkey:298795	1	668	2019-07-02 17:04:01.178786	\N	\N	100
state:ChathamIslandsTerritory:4033013	S2	Chatham Islands Territory	state	region:ChathamIslands:4033013	1	966	2019-07-02 08:07:34.199058	\N	\N	100
state:VerbanoCusioOssola:3164567	S2	Verbano-Cusio-Ossola	state	region:Piemonte:3170831	1	1026	2019-07-02 01:57:21.971808	\N	\N	100
state:NorteDeSantander:3673798	S2	Norte de Santander	state	country:Colombia:3686110	1	2681	2019-07-01 01:13:59.037551	\N	\N	100
state:Tuvalu:2110297	S2	Tuvalu	state	country:Tuvalu:2110297	1	947	2019-07-02 05:52:48.86763	\N	\N	100
sea:LigurianSea:3174724	S2	Ligurian Sea	sea	root	1	1535	2019-07-02 01:41:17.918976	\N	\N	100
state:ElParaiso:3610942	S2	El Paraíso	state	country:Honduras:3608932	1	1023	2019-07-02 02:17:37.914164	\N	\N	100
state:Hawalli:285628	S2	Hawalli	state	country:Kuwait:285570	1	2012	2019-06-30 19:20:28.858823	\N	\N	100
state:LaSpezia:3175080	S2	La Spezia	state	region:Liguria:3174725	1	1503	2019-07-02 01:41:17.919455	\N	\N	100
state:Planken:3042050	S2	Planken	state	country:Liechtenstein:3042058	1	506	2019-07-02 01:26:58.300325	\N	\N	100
state:SaintPaul:3575619	S2	Saint Paul	state	country:Dominica:3575830	1	1030	2019-07-02 00:12:17.351384	\N	\N	100
state:Morazan:3584317	S2	Morazán	state	country:ElSalvador:3585968	1	517	2019-07-02 02:16:36.536223	\N	\N	100
state:SoukAhras:2479213	S2	Souk Ahras	state	country:Algeria:2589581	1	2005	2019-07-02 01:42:36.353171	\N	\N	100
state:Intibuca:3608833	S2	Intibucá	state	country:Honduras:3608932	1	1042	2019-07-02 02:08:35.984405	\N	\N	100
country:Tuvalu:2110297	S2	Tuvalu	country	continent:Oceania:6255151	0	947	2019-07-02 05:52:48.86834	\N	\N	100
state:MubarakAlKabeer:7329411	S2	Mubarak Al-Kabeer	state	country:Kuwait:285570	1	2014	2019-06-30 19:20:28.859297	\N	\N	100
state:Choluteca:3613527	S2	Choluteca	state	country:Honduras:3608932	1	995	2019-07-02 01:43:15.576824	\N	\N	100
state:Zilinsky:3056506	S2	Žilinský	state	country:Slovakia:3057568	1	2074	2019-07-02 17:53:35.428274	\N	\N	100
state:Gaoual:2420825	S2	Gaoual	state	region:Boke:8335085	1	1526	2019-07-01 20:59:05.316254	\N	\N	100
state:Constantine:2501147	S2	Constantine	state	country:Algeria:2589581	1	1009	2019-07-02 01:39:24.66259	\N	\N	100
country:CaboVerde:3374766	S2	Cabo Verde	country	continent:Africa:6255146	0	1003	2019-07-02 02:18:13.738713	\N	\N	100
state:Telimele:2414925	S2	Télimélé	state	region:Kindia:8335088	1	502	2019-07-01 21:08:56.691832	\N	\N	100
state:Ruggell:3042047	S2	Ruggell	state	country:Liechtenstein:3042058	1	506	2019-07-02 01:26:58.300784	\N	\N	100
state:Gamprin:3042063	S2	Gamprin	state	country:Liechtenstein:3042058	1	506	2019-07-02 01:26:58.301235	\N	\N	100
state:Ouham:2383653	S2	Ouham	state	country:CentralAfricanRepublic:239880	1	7300	2019-06-30 18:19:48.89244	\N	\N	100
state:Skikda:2479532	S2	Skikda	state	country:Algeria:2589581	1	1509	2019-07-02 01:39:24.663588	\N	\N	100
state:AlAsimah:285788	S2	Al Asimah	state	country:Kuwait:285570	1	2014	2019-06-30 19:20:28.858347	\N	\N	100
state:AlFarwaniyah:285816	S2	Al Farwaniyah	state	country:Kuwait:285570	1	2015	2019-06-30 19:20:28.859762	\N	\N	100
state:Triesenberg:3042034	S2	Triesenberg	state	country:Liechtenstein:3042058	1	495	2019-07-02 01:26:58.303044	\N	\N	100
state:Mexico:3523272	S2	México	state	country:Mexico:3996063	1	2607	2019-07-02 10:26:23.355026	\N	\N	100
state:HodhElGharbi:2379024	S2	Hodh el Gharbi	state	country:Mauritania:2378080	1	5049	2019-07-01 21:01:11.068874	\N	\N	100
state:DistritoFederal:3527646	S2	Distrito Federal	state	country:Mexico:3996063	1	513	2019-07-02 10:26:23.354486	\N	\N	100
bay:EastKoreaBay	S2	East Korea Bay	bay	root	1	1550	2019-07-02 10:26:02.648301	\N	\N	100
month:09	S2	09	month	root	1	2604804	2019-09-01 03:16:46.520972	\N	\N	100
state:NuevaVizcaya:1697456	S2	Nueva Vizcaya	state	region:CagayanValley(RegionIi):	1	1018	2019-07-02 10:09:45.668502	\N	\N	100
state:Orellana:3830306	S2	Orellana	state	country:Ecuador:3658394	1	2572	2019-07-02 08:46:44.290629	\N	\N	100
state:Pohnpei:2081550	S2	Pohnpei	state	country:Micronesia:2081918	1	449	2019-07-02 09:09:12.229147	\N	\N	100
state:Gegharkunik:828261	S2	Gegharkunik	state	country:Armenia:174982	1	990	2019-07-01 16:19:50.463626	\N	\N	100
state:Bolivar:3688650	S2	Bolívar	state	country:Colombia:3686110	1	3406	2019-07-02 08:22:54.345413	\N	\N	100
state:ThreeKingsIslands:2181191	S2	Three Kings Islands	state	region:NewZealandOutlyingIslands:	1	395	2019-07-02 09:19:09.055789	\N	\N	100
country:Niue:4036232	S2	Niue	country	continent:Oceania:6255151	0	994	2019-07-02 08:06:25.585618	\N	\N	100
state:AmoronIMania:7670904	S2	Amoron'i Mania	state	country:Madagascar:1062947	1	2469	2019-06-30 15:42:45.759295	\N	\N	100
state:SaintAndrew:3488716	S2	Saint Andrew	state	country:Jamaica:3489940	1	2030	2019-06-30 10:15:29.092916	\N	\N	100
day:03	S2	03	day	root	1	938488	2019-07-03 04:25:54.486274	\N	\N	100
state:Morelos:3522961	S2	Morelos	state	country:Mexico:3996063	1	1042	2019-07-02 10:25:46.375805	\N	\N	100
state:Manchester:3489586	S2	Manchester	state	country:Jamaica:3489940	1	2046	2019-06-30 10:15:29.093555	\N	\N	100
country:Jamaica:3489940	S2	Jamaica	country	continent:NorthAmerica:6255149	0	3001	2019-06-30 10:15:29.095879	\N	\N	100
state:AlAhmadi:285839	S2	Al Ahmadi	state	country:Kuwait:285570	1	4033	2019-06-30 19:19:19.869574	\N	\N	100
state:Alessandria:3183298	S2	Alessandria	state	region:Piemonte:3170831	1	498	2019-07-02 01:50:07.346089	\N	\N	100
strait:StraitsOfFlorida:3558765	S2	Straits of Florida	strait	root	1	9961	2019-07-01 03:04:13.677201	\N	\N	100
state:Quirino:1691975	S2	Quirino	state	region:CagayanValley(RegionIi):	1	514	2019-07-02 10:20:32.794031	\N	\N	100
state:Hidalgo:3527115	S2	Hidalgo	state	country:Mexico:3996063	1	1734	2019-07-02 10:26:23.365803	\N	\N	100
state:NorthCaicos:3576957	S2	North Caicos	state	country:TurksAndCaicosIslands:3576916	1	995	2019-07-02 08:46:10.264359	\N	\N	100
country:Micronesia:2081918	S2	Micronesia	country	continent:Oceania:6255151	0	449	2019-07-02 09:09:12.229844	\N	\N	100
state:ProvidencialesAndWestCaicos:	S2	Providenciales and West Caicos	state	country:TurksAndCaicosIslands:3576916	1	993	2019-07-02 08:38:39.019751	\N	\N	100
state:Chuquisaca:3920177	S2	Chuquisaca	state	country:Bolivia:3923057	1	9457	2019-07-01 01:22:58.036321	\N	\N	100
state:Mayaguana:3571894	S2	Mayaguana	state	country:Bahamas:3572887	1	978	2019-07-02 08:31:32.089437	\N	\N	100
state:Paraiba:3393098	S2	Paraíba	state	country:Brazil:3469034	1	7761	2019-06-30 21:53:00.113608	\N	\N	100
state:MassaCarrara:3173767	S2	Massa-Carrara	state	region:Toscana:3165361	1	672	2019-07-02 01:53:31.219791	\N	\N	100
state:Central:2205272	S2	Central	state	country:Fiji:2205218	1	1001	2019-07-02 09:19:35.284438	\N	\N	100
state:Gnagna:2360516	S2	Gnagna	state	region:Est:6930709	1	520	2019-07-02 01:03:04.470942	\N	\N	100
state:SaintCatherine:3488711	S2	Saint Catherine	state	country:Jamaica:3489940	1	1023	2019-06-30 10:15:29.094674	\N	\N	100
state:SulawesiBarat:1996550	S2	Sulawesi Barat	state	country:Indonesia:1643084	1	2080	2019-07-02 09:11:58.762208	\N	\N	100
state:SaintElizabeth:3488708	S2	Saint Elizabeth	state	country:Jamaica:3489940	1	2969	2019-06-30 10:15:29.09411	\N	\N	100
state:SaintBarthelemy:3578476	S2	Saint Barthélemy	state	country:SaintBarthelemy:3578476	1	931	2019-06-30 10:10:41.935355	\N	\N	100
state:Clarendon:3490952	S2	Clarendon	state	country:Jamaica:3489940	1	2040	2019-06-30 10:15:29.095286	\N	\N	100
state:Northland:2185978	S2	Northland	state	region:NorthIsland:2185983	1	3251	2019-07-02 09:08:10.99444	\N	\N	100
state:Inagua:3572154	S2	Inagua	state	country:Bahamas:3572887	1	495	2019-07-02 11:34:18.062044	\N	\N	100
state:Agalega:448254	S2	Agaléga	state	country:Mauritius:934292	1	259	2022-06-08 13:18:29.321561	\N	\N	100
state:Hainan:1809054	S2	Hainan	state	region:SouthCentralChina:	1	3674	2019-07-02 12:12:30.999735	\N	\N	100
state:Dəvəci:586725	S2	Dəvəçi	state	region:GubaKhachmazEconomicRegion:	1	995	2019-06-30 20:05:47.734739	\N	\N	100
state:PapuaBarat:1996549	S2	Papua Barat	state	country:Indonesia:1643084	1	14545	2019-06-30 10:10:20.786867	\N	\N	100
state:BenTre:1587974	S2	Bến Tre	state	region:DongBangSongCuuLong:1574717	1	506	2019-07-02 12:22:06.914193	\N	\N	100
state:Muntinlupa:1699077	S2	Muntinlupa	state	region:NationalCapitalRegion:7521311	1	531	2019-07-02 10:24:36.439741	\N	\N	100
state:SanchezRamirez:3493192	S2	Sánchez Ramírez	state	country:DominicanRepublic:3508796	1	2044	2019-06-30 23:42:03.568564	\N	\N	100
state:Badghis:1147707	S2	Badghis	state	country:Afghanistan:1149361	1	2061	2019-07-02 14:05:05.712488	\N	\N	100
state:Seno:2355737	S2	Séno	state	region:Sahel:6930713	1	605	2019-07-02 01:00:33.975579	\N	\N	100
state:BangkaBelitung:1923047	S2	Bangka-Belitung	state	country:Indonesia:1643084	1	2531	2019-07-02 12:21:04.189665	\N	\N	100
state:Kankan:8335087	S2	Kankan	state	region:Kankan:8335087	1	2016	2019-06-30 22:44:38.203778	\N	\N	100
state:DistritoNacional:3496024	S2	Distrito Nacional	state	country:DominicanRepublic:3508796	1	1038	2019-06-30 23:42:24.680776	\N	\N	100
state:KepulauanRiau:1996551	S2	Kepulauan Riau	state	country:Indonesia:1643084	1	998	2019-07-02 12:40:23.133436	\N	\N	100
state:Drome:3020781	S2	Drôme	state	region:RhoneAlpes:11071625	1	1565	2019-07-02 19:04:52.233087	\N	\N	100
state:Istanbul:745042	S2	Istanbul	state	country:Turkey:298795	1	2066	2019-07-02 16:11:16.145393	\N	\N	100
state:Ouest:3719432	S2	Ouest	state	country:Haiti:3723988	1	1525	2019-07-02 11:22:04.924421	\N	\N	100
state:NuevaEcija:1697473	S2	Nueva Ecija	state	region:CentralLuzon(RegionIii):	1	528	2019-07-02 10:47:51.422346	\N	\N	100
state:Janakpur:1283316	S2	Janakpur	state	region:Central:7289707	1	1054	2019-07-02 13:28:27.761226	\N	\N	100
state:Coimbra:2740636	S2	Coimbra	state	region:Centro:11886822	1	973	2019-07-02 20:21:14.741185	\N	\N	100
bay:SharkBay:7839637	S2	Shark Bay	bay	root	1	1590	2019-07-02 11:16:27.691502	\N	\N	100
state:Aurora:1729926	S2	Aurora	state	region:CentralLuzon(RegionIii):	1	1029	2019-07-02 10:20:32.79474	\N	\N	100
state:Rodrigues:1547449	S2	Rodrigues	state	country:Mauritius:934292	1	494	2019-07-02 13:13:46.461989	\N	\N	100
state:SantoDomingo:6201373	S2	Santo Domingo	state	country:DominicanRepublic:3508796	1	1035	2019-06-30 23:42:24.682639	\N	\N	100
state:NinhThuan:1559971	S2	Ninh Thuận	state	region:DongNamBo:11497301	1	510	2019-07-02 12:45:07.686918	\N	\N	100
state:Mechi:1283047	S2	Mechi	state	region:East:7289708	1	1550	2019-07-02 13:23:42.255852	\N	\N	100
state:Burtnieku:7628372	S2	Burtnieku	state	region:Vidzeme:7639661	1	498	2019-06-30 10:14:09.057872	\N	\N	100
state:DakLak:1584169	S2	Đắk Lắk	state	region:TayNguyen:11497253	1	1522	2019-07-02 12:29:53.340799	\N	\N	100
state:KhanhHoa:1579634	S2	Khánh Hòa	state	region:NamTrungBo:11497252	1	510	2019-07-02 12:45:07.688291	\N	\N	100
state:Batangas:1726279	S2	Batangas	state	region:Calabarzon(RegionIvA):	1	512	2019-07-02 10:24:36.440394	\N	\N	100
state:Jizzakh:1484844	S2	Jizzakh	state	country:Uzbekistan:1512440	1	2571	2019-07-02 14:00:21.224761	\N	\N	100
state:MoyenChari:2427315	S2	Moyen-Chari	state	country:Chad:2434508	1	7168	2019-06-30 18:10:15.520946	\N	\N	100
state:Olancho:3604249	S2	Olancho	state	country:Honduras:3608932	1	2270	2019-07-02 01:42:33.286219	\N	\N	100
state:MonsenorNouel:3496274	S2	Monseñor Nouel	state	country:DominicanRepublic:3508796	1	1534	2019-06-30 23:42:24.681752	\N	\N	100
country:FrenchSouthernAndAntarcticLands:1546748	S2	French Southern and Antarctic Lands	country	continent:SevenSeas(OpenOcean):	0	2037	2019-07-01 13:06:21.799777	\N	\N	100
state:Badulla:1250614	S2	Badulla	state	region:UvaPalata:	1	1028	2019-07-02 12:26:32.862799	\N	\N	100
region:East:7289708	S2	East	region	country:Nepal:1282988	0	2953	2019-07-02 13:18:07.619829	\N	\N	100
state:Matara:1235845	S2	Mātara	state	region:DakunuPalata:	1	528	2019-07-02 12:26:32.861928	\N	\N	100
state:HimachalPradesh:1270101	S2	Himachal Pradesh	state	region:North:8335144	1	4706	2019-07-02 13:26:38.862404	\N	\N	100
state:Sikkim:1256312	S2	Sikkim	state	region:East:1272123	1	1031	2019-07-02 13:16:01.897653	\N	\N	100
state:QuangNgai:1568769	S2	Quảng Ngãi	state	region:NamTrungBo:11497252	1	531	2019-07-02 12:15:19.968498	\N	\N	100
state:BaieLazare:241439	S2	Baie Lazare	state	country:Seychelles:241170	1	485	2019-07-04 11:30:56.162015	\N	\N	100
state:AlDali:80383	S2	Al Dali'	state	country:Yemen:69543	1	525	2019-07-02 15:37:50.311603	\N	\N	100
state:Ngozi:430567	S2	Ngozi	state	country:Burundi:433561	1	1056	2019-07-02 15:50:07.688463	\N	\N	100
state:Sagarmatha:1282836	S2	Sagarmatha	state	region:East:7289708	1	1047	2019-07-02 13:24:31.752729	\N	\N	100
state:BinhDuong:1905475	S2	Bình Dương	state	region:DongNamBo:11497301	1	504	2019-07-02 12:16:29.707948	\N	\N	100
strait:QiongzhouStrait	S2	Qiongzhou Strait	strait	root	1	1004	2019-07-02 12:34:09.035564	\N	\N	100
state:ArRaqqah:172957	S2	Ar Raqqah	state	country:Syria:163843	1	1807	2019-07-02 16:27:10.170588	\N	\N	100
state:Ebonyi:2595345	S2	Ebonyi	state	country:Nigeria:2328926	1	1515	2019-07-04 15:43:50.619223	\N	\N	100
state:GiaLai:1581088	S2	Gia Lai	state	region:NamTrungBo:11497252	1	2107	2019-07-02 12:16:42.940063	\N	\N	100
state:Bhojpur:1283182	S2	Bhojpur	state	region:East:7289708	1	355	2019-07-02 13:18:07.619368	\N	\N	100
state:Hoima:233312	S2	Hoima	state	region:Western:8260675	1	1051	2019-07-02 16:28:15.518493	\N	\N	100
state:Lyantonde:229599	S2	Lyantonde	state	region:Central:234594	1	1539	2019-07-02 16:22:49.743384	\N	\N	100
state:Muyinga:431747	S2	Muyinga	state	country:Burundi:433561	1	2022	2019-07-02 15:50:07.688072	\N	\N	100
state:Yumbe:225835	S2	Yumbe	state	region:Northern:8260674	1	515	2019-07-02 16:29:32.084678	\N	\N	100
state:PotaroSiparuni:3376386	S2	Potaro-Siparuni	state	country:Guyana:3378535	1	2026	2019-06-30 23:41:31.279618	\N	\N	100
strait:Bosporus:5906016	S2	Bosporus	strait	root	1	1031	2019-07-02 16:26:47.597377	\N	\N	100
state:Kanungu:232287	S2	Kanungu	state	region:Western:8260675	1	518	2019-07-02 15:48:06.620203	\N	\N	100
state:Kirundo:432455	S2	Kirundo	state	country:Burundi:433561	1	519	2019-07-02 15:56:42.595955	\N	\N	100
state:Mpumalanga:1085595	S2	Mpumalanga	state	country:SouthAfrica:953987	1	12265	2019-07-01 16:28:02.887279	\N	\N	100
state:Kibale:231617	S2	Kibale	state	region:Western:8260675	1	456	2019-07-02 16:25:13.740983	\N	\N	100
country:SaintVincentAndTheGrenadines:3577815	S2	Saint Vincent and the Grenadines	country	continent:NorthAmerica:6255149	0	501	2019-07-03 22:23:42.910531	\N	\N	100
state:Cankuzo:427700	S2	Cankuzo	state	country:Burundi:433561	1	2159	2019-07-02 15:50:07.687587	\N	\N	100
state:Kisoro:230993	S2	Kisoro	state	region:Western:8260675	1	516	2019-07-02 15:54:31.20766	\N	\N	100
state:DakNong:8675920	S2	Đắk Nông	state	region:TayNguyen:11497253	1	507	2019-07-02 12:16:32.804566	\N	\N	100
state:Mubende:443358	S2	Mubende	state	region:Central:234594	1	903	2019-07-02 16:25:13.742132	\N	\N	100
state:Kabale:233070	S2	Kabale	state	region:Western:8260675	1	515	2019-07-02 15:32:40.755658	\N	\N	100
state:Anosy:7670910	S2	Anosy	state	country:Madagascar:1062947	1	4681	2019-06-30 15:38:21.34271	\N	\N	100
state:Sennar:408669	S2	Sennar	state	region:BlueNile:408654	1	3107	2019-07-02 16:24:06.678398	\N	\N	100
state:Xizi:	S2	Xizı	state	region:AbsheronEconomicRegion:	1	1014	2019-06-30 20:05:47.735683	\N	\N	100
state:KigaliCity:6413338	S2	Kigali City	state	country:Rwanda:49518	1	1025	2019-07-02 15:32:40.75752	\N	\N	100
state:BujumburaMairie:7303939	S2	Bujumbura Mairie	state	country:Burundi:433561	1	600	2019-07-02 15:49:37.40077	\N	\N	100
state:CharenteMaritime:3026644	S2	Charente-Maritime	state	region:PoitouCharentes:11071620	1	530	2019-07-03 20:03:49.811808	\N	\N	100
state:KonTum:1565088	S2	Kon Tum	state	region:NamTrungBo:11497252	1	1101	2019-07-02 12:16:16.41616	\N	\N	100
state:Gezira:408648	S2	Gezira	state	region:BlueNile:408654	1	1599	2019-07-02 16:24:57.060145	\N	\N	100
state:Western:6413340	S2	Western	state	country:Rwanda:49518	1	1037	2019-07-02 15:49:13.4045	\N	\N	100
state:Southern:6413341	S2	Southern	state	country:Rwanda:49518	1	1053	2019-07-02 15:49:13.404087	\N	\N	100
state:Nebbi:227904	S2	Nebbi	state	region:Northern:8260674	1	525	2019-07-02 16:39:17.796508	\N	\N	100
state:Rukungiri:226600	S2	Rukungiri	state	region:Western:8260675	1	511	2019-07-02 16:00:40.985863	\N	\N	100
state:Alborz:7648907	S2	Alborz	state	country:Iran:130758	1	1020	2019-07-02 14:28:54.219933	\N	\N	100
state:Potenza:3170025	S2	Potenza	state	region:Basilicata:3182306	1	1727	2019-07-02 17:54:46.738229	\N	\N	100
state:Bender:618577	S2	Bender	state	country:Moldova:617790	1	1548	2019-07-02 16:15:07.233302	\N	\N	100
state:Singida:149876	S2	Singida	state	country:Tanzania:149590	1	6659	2019-07-01 16:59:20.987589	\N	\N	100
state:KoprivnickoKrizevacka:3337518	S2	Koprivničko-Križevačka	state	country:Croatia:3202326	1	1039	2019-07-02 17:52:42.071328	\N	\N	100
state:Ihorombe:7670907	S2	Ihorombe	state	country:Madagascar:1062947	1	2424	2019-06-30 15:44:26.762707	\N	\N	100
state:Northern:6413339	S2	Northern	state	country:Rwanda:49518	1	1030	2019-07-02 15:32:40.758123	\N	\N	100
state:Isingiro:7911405	S2	Isingiro	state	region:Western:8260675	1	1031	2019-07-02 16:40:02.892687	\N	\N	100
state:Faryab:1142226	S2	Faryab	state	country:Afghanistan:1149361	1	4057	2019-07-02 14:01:29.655	\N	\N	100
region:TrentinoAltoAdige:3165244	S2	Trentino-Alto Adige	region	country:Italy:3175395	0	2664	2019-07-02 01:44:05.583819	\N	\N	100
state:Adjumani:235489	S2	Adjumani	state	region:Northern:8260674	1	518	2019-07-02 16:34:27.990899	\N	\N	100
state:Trenciansky:3343957	S2	Trenciansky	state	country:Slovakia:3057568	1	1042	2019-07-02 17:53:35.429054	\N	\N	100
state:StefanVoda:617283	S2	Ștefan Vodă	state	country:Moldova:617790	1	1031	2019-07-02 16:16:42.259115	\N	\N	100
state:Jwaneng:8308627	S2	Jwaneng	state	country:Botswana:933860	1	1030	2019-07-02 17:11:44.925234	\N	\N	100
state:Karuzi:428218	S2	Karuzi	state	country:Burundi:433561	1	557	2019-07-02 15:50:07.688901	\N	\N	100
state:Gedarif:408649	S2	Gedarif	state	region:Kassala:408663	1	5184	2019-07-02 16:31:46.62512	\N	\N	100
state:Foggia:3176884	S2	Foggia	state	region:Apulia:3169778	1	686	2019-07-02 17:50:34.294636	\N	\N	100
state:AnseBoileau:241449	S2	Anse Boileau	state	country:Seychelles:241170	1	484	2019-07-04 11:30:56.162478	\N	\N	100
state:Buliisa:8657545	S2	Buliisa	state	region:Western:8260675	1	521	2019-07-02 16:49:38.16442	\N	\N	100
state:GambelaPeoples:444183	S2	Gambela Peoples	state	country:Ethiopia:337996	1	2647	2019-07-02 16:40:51.425959	\N	\N	100
state:Kassala:408663	S2	Kassala	state	region:Kassala:408663	1	6262	2019-07-02 16:19:42.160223	\N	\N	100
region:SouthEast:	S2	South-East	region	country:Ireland:2963597	0	2200	2019-06-30 21:42:09.472289	\N	\N	100
state:Nwoya:448243	S2	Nwoya	state	region:Northern:8260674	1	1053	2019-07-02 16:49:38.165863	\N	\N	100
state:Kirovohrad:705811	S2	Kirovohrad	state	country:Ukraine:690791	1	5480	2019-07-02 16:07:10.699929	\N	\N	100
state:Bangui:2596686	S2	Bangui	state	country:CentralAfricanRepublic:239880	1	1014	2019-07-02 16:15:36.76841	\N	\N	100
state:LaAraucania:3899463	S2	La Araucanía	state	country:Chile:3895114	1	5719	2019-07-01 01:39:12.29984	\N	\N	100
state:Baranya:3055399	S2	Baranya	state	region:SouthernTransdanubia:	1	1552	2019-07-02 17:48:57.466267	\N	\N	100
state:Pescara:3171163	S2	Pescara	state	region:Abruzzo:3183560	1	522	2019-07-02 17:50:24.235091	\N	\N	100
state:Kyankwanzi:8657527	S2	Kyankwanzi	state	region:Central:234594	1	1016	2019-07-02 16:35:25.681303	\N	\N	100
region:Northeast:10575550	S2	Northeast	region	country:India:6269134	0	29446	2019-06-30 17:00:03.915665	\N	\N	100
state:AzZawiyah:2218972	S2	Az Zawiyah	state	country:Libya:2215636	1	1025	2019-07-02 17:52:41.820879	\N	\N	100
state:Sila:7603257	S2	Sila	state	country:Chad:2434508	1	3568	2019-07-02 16:45:05.991285	\N	\N	100
state:Arua:235039	S2	Arua	state	region:Northern:8260674	1	540	2019-07-02 16:49:04.599133	\N	\N	100
state:Kyenjojo:230299	S2	Kyenjojo	state	region:Western:8260675	1	180	2019-07-02 16:25:56.763251	\N	\N	100
state:BlueNile:408654	S2	Blue Nile	state	region:BlueNile:408654	1	5504	2019-07-02 16:22:12.839935	\N	\N	100
state:Machinga:927642	S2	Machinga	state	country:Malawi:927384	1	1024	2019-06-30 20:01:56.416543	\N	\N	100
state:SibenskoKninska:3337525	S2	Šibensko-Kninska	state	country:Croatia:3202326	1	514	2019-07-02 17:50:14.142906	\N	\N	100
state:Ouaddai:242246	S2	Ouaddaï	state	country:Chad:2434508	1	3040	2019-07-02 16:45:05.991964	\N	\N	100
state:Zebbug:8299763	S2	Żebbuġ	state	region:Gozo:2562619	1	525	2019-07-02 17:52:19.083171	\N	\N	100
state:Ntoroko:8644154	S2	Ntoroko	state	region:Western:8260675	1	515	2019-07-02 15:54:40.439499	\N	\N	100
state:Rutana:434147	S2	Rutana	state	country:Burundi:433561	1	602	2019-07-02 15:55:25.428115	\N	\N	100
region:WesternTransdanubia:	S2	Western Transdanubia	region	country:Hungary:719819	0	2076	2019-06-30 18:19:06.361129	\N	\N	100
state:Kasese:232066	S2	Kasese	state	region:Western:8260675	1	520	2019-07-02 15:48:14.207856	\N	\N	100
state:SchleswigHolstein:2838632	S2	Schleswig-Holstein	state	country:Germany:2921044	1	2235	2019-07-02 01:46:26.7438	\N	\N	100
state:Bari:3182350	S2	Bari	state	region:Apulia:3169778	1	1037	2019-07-02 18:05:18.956136	\N	\N	100
country:Liberia:2275384	S2	Liberia	country	continent:Africa:6255146	0	10774	2019-06-30 21:57:30.295127	\N	\N	100
state:Oueme:2392325	S2	Ouémé	state	country:Benin:2395170	1	1006	2019-06-30 18:00:20.97347	\N	\N	100
state:Xaghra:8299759	S2	Xagħra	state	region:Gozo:2562619	1	521	2019-07-02 17:58:59.510892	\N	\N	100
state:AlJifarah:2473644	S2	Al Jifarah	state	country:Libya:2215636	1	1536	2019-07-02 17:55:40.43914	\N	\N	100
state:BanjaLuka:3294893	S2	Banja Luka	state	region:RepuplikaSrpska:	1	1053	2019-07-02 17:57:20.435392	\N	\N	100
state:Sangha:2255329	S2	Sangha	state	country:Congo:2260494	1	7707	2019-06-30 18:08:39.315866	\N	\N	100
state:Chlef:2501296	S2	Chlef	state	country:Algeria:2589581	1	1031	2019-07-02 18:27:51.550454	\N	\N	100
state:AndorraLaVella:3041566	S2	Andorra la Vella	state	country:Andorra:3041565	1	1027	2019-06-30 21:08:15.823034	\N	\N	100
state:Kandavas:7628311	S2	Kandavas	state	region:Riga:456173	1	1026	2019-07-02 18:03:51.69771	\N	\N	100
state:Kocenu:7628374	S2	Kocenu	state	region:Vidzeme:7639661	1	510	2019-07-02 18:08:39.549342	\N	\N	100
state:Heves:720002	S2	Heves	state	region:NorthernHungary:	1	1033	2019-07-02 17:56:41.047088	\N	\N	100
state:Somogy:3045226	S2	Somogy	state	region:SouthernTransdanubia:	1	1040	2019-07-02 17:52:42.073887	\N	\N	100
state:UnaSana:3343704	S2	Una-Sana	state	region:FederacijaBosnaIHercegovina:	1	1037	2019-07-02 17:56:30.756809	\N	\N	100
state:Veszprem:3042925	S2	Veszprém	state	region:CentralTransdanubia:	1	521	2019-07-02 17:56:12.196078	\N	\N	100
state:Nouakchott:2377449	S2	Nouakchott	state	country:Mauritania:2378080	1	1988	2019-07-02 20:19:27.58026	\N	\N	100
state:Frosinone:3176513	S2	Frosinone	state	region:Lazio:3174976	1	1040	2019-07-02 17:51:25.028113	\N	\N	100
state:AlpesDeHauteProvence:3038050	S2	Alpes-de-Haute-Provence	state	region:ProvenceAlpesCoteDAzur:2985244	1	544	2019-07-02 19:04:52.231799	\N	\N	100
state:Mostaganem:2487130	S2	Mostaganem	state	country:Algeria:2589581	1	1556	2019-07-02 18:40:55.146899	\N	\N	100
state:Loire:2997870	S2	Loire	state	region:RhoneAlpes:11071625	1	681	2019-07-02 19:08:32.543156	\N	\N	100
state:GyorMosonSopron:3051977	S2	Gyor-Moson-Sopron	state	region:WesternTransdanubia:	1	1041	2019-07-02 17:49:34.088151	\N	\N	100
region:Gozo:2562619	S2	Gozo	region	country:Malta:2562770	0	1044	2019-07-02 17:52:19.083674	\N	\N	100
state:Bauska:461114	S2	Bauska	state	region:Zemgale:7639660	1	518	2019-07-02 18:13:44.389525	\N	\N	100
state:Gerona:6355230	S2	Gerona	state	region:Cataluna:3336901	1	537	2019-07-02 19:10:22.215963	\N	\N	100
state:Pest:3046431	S2	Pest	state	region:CentralHungary:	1	2586	2019-07-02 17:55:35.518465	\N	\N	100
state:Avellino:6457405	S2	Avellino	state	region:Campania:3181042	1	167	2019-07-02 17:54:46.73729	\N	\N	100
state:Vaucluse:2970554	S2	Vaucluse	state	region:ProvenceAlpesCoteDAzur:2985244	1	1055	2019-07-02 19:07:03.888579	\N	\N	100
state:Nassarawa:2595348	S2	Nassarawa	state	country:Nigeria:2328926	1	2584	2019-07-02 18:30:31.298917	\N	\N	100
state:FederalCapitalTerritory:2352776	S2	Federal Capital Territory	state	country:Nigeria:2328926	1	2039	2019-07-02 18:32:00.787293	\N	\N	100
state:Kossi:2359027	S2	Kossi	state	region:BoucleDuMouhoun:6930701	1	1040	2019-07-02 20:27:30.61535	\N	\N	100
state:Agrigento:2525763	S2	Agrigento	state	region:Sicily:2523119	1	523	2019-07-02 17:58:53.340347	\N	\N	100
state:Olaines:7628320	S2	Olaines	state	region:Riga:456173	1	522	2019-07-02 18:13:44.388767	\N	\N	100
state:Salerno:3168670	S2	Salerno	state	region:Campania:3181042	1	526	2019-07-02 17:57:28.357381	\N	\N	100
state:Beja:2472770	S2	Béja	state	country:Tunisia:2464461	1	1327	2019-06-30 18:05:03.776429	\N	\N	100
state:HautSassandra:2597319	S2	Haut-Sassandra	state	country:IvoryCoast:2287781	1	1025	2019-07-02 19:32:36.882675	\N	\N	100
state:Luxembourg:2791993	S2	Luxembourg	state	region:Walloon:3337387	1	520	2019-07-02 19:06:34.733905	\N	\N	100
state:Moyo:229059	S2	Moyo	state	region:Northern:8260674	1	515	2019-07-02 16:35:06.676446	\N	\N	100
state:Siracusa:2523082	S2	Siracusa	state	region:Sicily:2523119	1	1382	2019-07-02 18:03:06.042811	\N	\N	100
state:ZenicaDoboj:3343722	S2	Zenica-Doboj	state	region:FederacijaBosnaIHercegovina:	1	529	2019-07-02 17:57:39.227107	\N	\N	100
state:Herault:3013500	S2	Hérault	state	region:LanguedocRoussillon:11071623	1	1029	2019-07-02 19:08:55.477911	\N	\N	100
state:CuanzaSul:3349234	S2	Cuanza Sul	state	country:Angola:3351879	1	4424	2019-07-02 18:03:12.238115	\N	\N	100
state:Coquimbo:3893623	S2	Coquimbo	state	country:Chile:3895114	1	4628	2019-07-02 00:44:35.858076	\N	\N	100
state:Edo:2565343	S2	Edo	state	country:Nigeria:2328926	1	3081	2019-07-02 18:33:06.304015	\N	\N	100
state:Delta:2565341	S2	Delta	state	country:Nigeria:2328926	1	1552	2019-07-02 18:33:06.304498	\N	\N	100
state:Ticino:2658370	S2	Ticino	state	country:Switzerland:2658434	1	1015	2019-07-02 01:57:21.974898	\N	\N	100
region:CentralHungary:	S2	Central Hungary	region	country:Hungary:719819	0	2588	2019-07-02 17:55:35.518972	\N	\N	100
state:Campobasso:3171172	S2	Campobasso	state	region:Molise:3173222	1	1039	2019-07-02 17:58:20.625441	\N	\N	100
state:Alojas:7628368	S2	Alojas	state	region:Riga:456173	1	1012	2019-07-02 18:16:22.017796	\N	\N	100
state:Hrastnik:3199296	S2	Hrastnik	state	region:Zasavska:	1	497	2019-07-02 18:04:26.92773	\N	\N	100
state:SplitskoDalmatinska:3337527	S2	Splitsko-Dalmatinska	state	country:Croatia:3202326	1	1906	2019-07-02 17:55:10.547459	\N	\N	100
state:Mazsalacas:7628371	S2	Mazsalacas	state	region:Vidzeme:7639661	1	1013	2019-07-02 18:16:22.017299	\N	\N	100
state:Saldus:455888	S2	Saldus	state	region:Kurzeme:460496	1	1025	2019-07-02 18:05:52.917379	\N	\N	100
state:Nograd:3047348	S2	Nógrád	state	region:NorthernHungary:	1	1033	2019-07-02 18:13:07.076539	\N	\N	100
state:Quezaltenango:3590978	S2	Quezaltenango	state	country:Guatemala:3595528	1	511	2019-07-02 23:31:39.053822	\N	\N	100
state:Makamba:422233	S2	Makamba	state	country:Burundi:433561	1	1101	2019-07-02 15:55:25.42736	\N	\N	100
state:SaoneEtLoire:2976082	S2	Saône-et-Loire	state	region:Bourgogne:11071619	1	1049	2019-07-02 19:10:20.321216	\N	\N	100
state:Dakar:2253350	S2	Dakar	state	country:Senegal:2245662	1	1008	2019-07-02 20:25:06.762668	\N	\N	100
state:Vaupes:3666254	S2	Vaupés	state	country:Colombia:3686110	1	5284	2019-07-02 22:55:25.628821	\N	\N	100
state:Ardeche:3037147	S2	Ardèche	state	region:RhoneAlpes:11071625	1	522	2019-07-02 19:18:52.770642	\N	\N	100
state:Marahoue:2597332	S2	Marahoué	state	country:IvoryCoast:2287781	1	1528	2019-07-02 19:41:04.158734	\N	\N	100
state:Tinian:4041650	S2	Tinian	state	country:NorthernMarianaIslands:4041467	1	490	2019-07-02 20:52:22.630348	\N	\N	100
region:TayNguyen:11497253	S2	Tây Nguyên	region	country:Vietnam:1562822	0	3552	2019-07-02 12:16:32.805076	\N	\N	100
state:NewProvidence:3571815	S2	New Providence	state	country:Bahamas:3572887	1	996	2019-07-02 23:55:35.938475	\N	\N	100
state:CiegoDeAvila:3564175	S2	Ciego de Ávila	state	country:Cuba:3562981	1	505	2019-07-02 23:52:45.053634	\N	\N	100
state:IslesOfScilly:2638384	S2	Isles of Scilly	state	region:SouthWest:11591956	1	501	2019-07-02 19:32:58.787718	\N	\N	100
state:BenshangulGumaz:444181	S2	Benshangul-Gumaz	state	country:Ethiopia:337996	1	6487	2019-07-01 16:14:01.827854	\N	\N	100
state:VianaDoCastelo:2732772	S2	Viana do Castelo	state	region:Norte:3372843	1	1003	2019-07-02 20:15:46.298076	\N	\N	100
state:Castellon:3125881	S2	Castellón	state	region:Valenciana:2593113	1	1021	2019-06-30 21:05:47.949162	\N	\N	100
state:WalloonBrabant:3333251	S2	Walloon Brabant	state	region:Walloon:3337387	1	492	2019-07-02 19:18:13.286546	\N	\N	100
state:MahaicaBerbice:3378840	S2	Mahaica-Berbice	state	country:Guyana:3378535	1	3906	2019-07-02 23:56:10.217687	\N	\N	100
state:PlainesWilhems:934166	S2	Plaines Wilhems	state	country:Mauritius:934292	1	486	2019-07-03 13:42:31.756203	\N	\N	100
state:Kiribati:4030945	S2	Kiribati	state	country:Kiribati:4030945	1	496	2019-07-03 04:16:39.249096	\N	\N	100
state:Ayacucho:3947018	S2	Ayacucho	state	country:Peru:3932488	1	3890	2019-07-02 22:58:15.058119	\N	\N	100
state:Carabobo:3646751	S2	Carabobo	state	country:Venezuela:3625428	1	671	2019-07-02 23:46:57.690826	\N	\N	100
state:Banjul:2413875	S2	Banjul	state	country:Gambia:2413451	1	495	2019-07-02 20:19:27.833862	\N	\N	100
state:Rota:4041523	S2	Rota	state	country:NorthernMarianaIslands:4041467	1	988	2019-07-02 20:30:23.358023	\N	\N	100
state:Kara:2597439	S2	Kara	state	country:Togo:2363686	1	3078	2019-07-02 00:18:12.735462	\N	\N	100
state:Curacao:3513790	S2	Curaçao	state	country:Curacao:3513790	1	1010	2019-07-02 23:50:22.730044	\N	\N	100
state:Saipan:7828758	S2	Saipan	state	country:NorthernMarianaIslands:4041467	1	490	2019-07-02 20:52:22.63083	\N	\N	100
country:Kiribati:4030945	S2	Kiribati	country	continent:Oceania:6255151	0	496	2019-07-03 04:16:39.249792	\N	\N	100
state:Enugu:2565344	S2	Enugu	state	country:Nigeria:2328926	1	2545	2019-07-02 18:31:35.181272	\N	\N	100
state:Luxembourg:2960314	S2	Luxembourg	state	country:Luxembourg:2960313	1	1028	2019-07-02 19:18:50.698423	\N	\N	100
state:LowerRiver:2412716	S2	Lower River	state	country:Gambia:2413451	1	1526	2019-07-02 20:19:27.834459	\N	\N	100
state:PuertoRico:4566966	S2	Puerto Rico	state	country:PuertoRico:4566966	1	1048	2019-07-02 23:36:58.062629	\N	\N	100
state:Leraba:2597263	S2	Léraba	state	region:Cascades:6930703	1	1036	2019-07-02 19:29:32.692786	\N	\N	100
state:HauteLoire:3013760	S2	Haute-Loire	state	region:Auvergne:11071625	1	525	2019-07-02 19:18:52.771413	\N	\N	100
state:HarbourIsland:3572238	S2	Harbour Island	state	country:Bahamas:3572887	1	495	2019-07-02 23:50:23.638858	\N	\N	100
state:RiviereDuRempart:934090	S2	Rivière du Rempart	state	country:Mauritius:934292	1	484	2019-07-03 13:42:31.756638	\N	\N	100
state:Kenedougou:2359569	S2	Kénédougou	state	region:HautsBassins:6930710	1	1018	2019-07-02 20:31:49.637892	\N	\N	100
state:EscaldesEngordany:3338529	S2	Escaldes-Engordany	state	country:Andorra:3041565	1	1027	2019-06-30 21:08:15.823696	\N	\N	100
state:WestCoast:2411683	S2	West Coast	state	country:Gambia:2413451	1	1008	2019-07-02 20:19:27.83556	\N	\N	100
state:Santarem:2263478	S2	Santarém	state	region:Alentejo:2268252	1	617	2019-07-02 19:24:07.390092	\N	\N	100
country:Curacao:3513790	S2	Curaçao	country	continent:NorthAmerica:6255149	0	1010	2019-07-02 23:50:22.730524	\N	\N	100
state:Herrera:3708710	S2	Herrera	state	country:Panama:3703430	1	503	2019-07-03 00:06:57.285771	\N	\N	100
state:CheshireWestAndChester:7290537	S2	Cheshire West and Chester	state	region:NorthWest:2641227	1	496	2019-07-02 19:38:22.624213	\N	\N	100
state:Thies:2244800	S2	Thiès	state	country:Senegal:2245662	1	1207	2019-07-02 20:22:09.631303	\N	\N	100
state:Nickerie:3383438	S2	Nickerie	state	country:Suriname:3382998	1	968	2019-07-02 23:55:22.788523	\N	\N	100
state:Encamp:3040684	S2	Encamp	state	country:Andorra:3041565	1	1028	2019-06-30 21:08:15.824339	\N	\N	100
state:Diekirch:2960655	S2	Diekirch	state	country:Luxembourg:2960313	1	1024	2019-07-02 19:06:34.73196	\N	\N	100
state:Anambra:2349961	S2	Anambra	state	country:Nigeria:2328926	1	1025	2019-07-02 18:44:39.81256	\N	\N	100
state:IsleOfMan:3042225	S2	Isle of Man	state	country:IsleOfMan:3042225	1	996	2019-07-02 19:41:25.584629	\N	\N	100
state:Louth:2962793	S2	Louth	state	region:Border:	1	496	2019-07-02 19:36:07.576857	\N	\N	100
state:Cojedes:3645386	S2	Cojedes	state	country:Venezuela:3625428	1	834	2019-07-02 23:39:13.472165	\N	\N	100
state:VinhPhuc:1905856	S2	Vĩnh Phúc	state	region:DongBangSongHong:	1	502	2019-07-03 11:11:48.695777	\N	\N	100
state:SiSaKet:1606238	S2	Si Sa Ket	state	region:Northeastern:	1	514	2019-07-03 11:37:04.7845	\N	\N	100
state:KrongPreahSihanouk:1899262	S2	Krong Preah Sihanouk	state	country:Cambodia:1831722	1	1001	2019-07-03 11:26:52.422053	\N	\N	100
state:Yaracuy:3625210	S2	Yaracuy	state	country:Venezuela:3625428	1	659	2019-07-02 23:40:22.911671	\N	\N	100
state:BuriRam:1611452	S2	Buri Ram	state	region:Northeastern:	1	519	2019-07-03 11:13:18.815438	\N	\N	100
state:Narathiwat:1608408	S2	Narathiwat	state	region:Southern:10234924	1	507	2019-07-03 11:27:18.580656	\N	\N	100
state:Cochabamba:3919966	S2	Cochabamba	state	country:Bolivia:3923057	1	8400	2019-07-01 01:36:38.810496	\N	\N	100
state:SakonNakhon:1606789	S2	Sakon Nakhon	state	region:Northeastern:	1	523	2019-07-03 11:30:45.055005	\N	\N	100
state:Yala:1604869	S2	Yala	state	region:Southern:10234924	1	1001	2019-07-03 11:13:57.10406	\N	\N	100
state:Putrajaya:1996552	S2	Putrajaya	state	country:Malaysia:1733045	1	1005	2019-07-03 11:19:46.445424	\N	\N	100
state:MooreSIsland:8030550	S2	Moore's Island	state	country:Bahamas:3572887	1	497	2019-07-02 23:50:43.759247	\N	\N	100
state:Tataouine:2464698	S2	Tataouine	state	country:Tunisia:2464461	1	5206	2019-06-30 18:06:23.328285	\N	\N	100
state:NgobeBugle:7303688	S2	Ngöbe Buglé	state	country:Panama:3703430	1	1017	2019-07-03 00:03:48.494351	\N	\N	100
state:Surin:1606029	S2	Surin	state	region:Northeastern:	1	519	2019-07-03 11:35:23.888567	\N	\N	100
state:KualaLumpur:1733046	S2	Kuala Lumpur	state	country:Malaysia:1733045	1	1002	2019-07-03 11:19:46.445943	\N	\N	100
state:Yogyakarta:1621176	S2	Yogyakarta	state	country:Indonesia:1643084	1	543	2019-07-03 11:23:48.13756	\N	\N	100
state:Tottori:1849890	S2	Tottori	state	region:Chugoku:1864492	1	1063	2019-07-03 08:22:54.304347	\N	\N	100
state:Bonaire:7609816	S2	Bonaire	state	country:Netherlands:2750405	1	502	2019-07-02 23:50:22.731326	\N	\N	100
state:DavaoOriental:1715342	S2	Davao Oriental	state	region:Davao(RegionXi):	1	1512	2019-07-03 08:07:30.471903	\N	\N	100
state:SpanishWells:8030558	S2	Spanish Wells	state	country:Bahamas:3572887	1	496	2019-07-02 23:50:23.639372	\N	\N	100
state:CentralAbaco:8030542	S2	Central Abaco	state	country:Bahamas:3572887	1	501	2019-07-02 23:50:43.761425	\N	\N	100
country:IsleOfMan:3042225	S2	Isle of Man	country	continent:Europe:6255148	0	996	2019-07-02 19:41:25.585166	\N	\N	100
state:BanteayMeanchey:1899273	S2	Bântéay Méanchey	state	country:Cambodia:1831722	1	1003	2019-07-03 11:13:11.857536	\N	\N	100
state:Kelantan:1733044	S2	Kelantan	state	country:Malaysia:1733045	1	2023	2019-07-03 11:14:11.99661	\N	\N	100
state:Pouthisat:1821301	S2	Pouthisat	state	country:Cambodia:1831722	1	2014	2019-07-03 11:10:55.382747	\N	\N	100
state:Selangor:1733037	S2	Selangor	state	country:Malaysia:1733045	1	1512	2019-07-03 11:19:46.446421	\N	\N	100
state:NanaMambere:2384205	S2	Nana-Mambéré	state	country:CentralAfricanRepublic:239880	1	4519	2019-06-30 18:20:30.130356	\N	\N	100
state:LaCoruna:3119840	S2	La Coruña	state	region:Galicia:3336902	1	1843	2019-07-02 19:29:55.481398	\N	\N	100
state:Magdalena:3675686	S2	Magdalena	state	country:Colombia:3686110	1	2766	2019-07-02 08:41:45.677183	\N	\N	100
state:Fromager:2597330	S2	Fromager	state	country:IvoryCoast:2287781	1	1531	2019-07-02 19:41:04.158187	\N	\N	100
state:RoiEt:1607000	S2	Roi Et	state	region:Northeastern:	1	505	2019-07-03 11:18:15.358273	\N	\N	100
state:MahaSarakham:1608899	S2	Maha Sarakham	state	region:Northeastern:	1	504	2019-07-03 11:34:11.533751	\N	\N	100
state:Kinmen:1676511	S2	Kinmen	state	region:FujianProvince:	1	494	2019-07-03 10:28:21.156556	\N	\N	100
state:Rocha:3440771	S2	Rocha	state	country:Uruguay:3439705	1	1548	2019-07-02 20:46:03.131091	\N	\N	100
state:Niuas:7668055	S2	Niuas	state	country:Tonga:4032283	1	491	2019-07-03 04:14:40.424544	\N	\N	100
state:Galguduud:59362	S2	Galguduud	state	country:Somalia:51537	1	4816	2019-07-01 19:59:14.386337	\N	\N	100
state:Croydon:3333140	S2	Croydon	state	region:GreaterLondon:2648110	1	1015	2019-07-01 19:38:33.252416	\N	\N	100
state:Mukdahan:1608595	S2	Mukdahan	state	region:Northeastern:	1	509	2019-07-03 11:32:54.168665	\N	\N	100
state:Aude:3036264	S2	Aude	state	region:LanguedocRoussillon:11071623	1	1113	2019-06-30 21:26:45.441739	\N	\N	100
state:Ambeno:1651539	S2	Ambeno	state	country:TimorLeste:1966436	1	499	2019-07-03 08:30:45.728236	\N	\N	100
state:Coronie:3384397	S2	Coronie	state	country:Suriname:3382998	1	966	2019-07-02 23:55:22.787994	\N	\N	100
state:Kalasin:1610468	S2	Kalasin	state	region:Northeastern:	1	508	2019-07-03 11:26:21.321878	\N	\N	100
state:OtdarMeanChey:	S2	Otdar Mean Chey	state	country:Cambodia:1831722	1	498	2019-07-03 11:32:51.553857	\N	\N	100
state:Pasco:3932834	S2	Pasco	state	country:Peru:3932488	1	1653	2019-07-01 00:56:46.802832	\N	\N	100
state:Guaviare:3681344	S2	Guaviare	state	country:Colombia:3686110	1	7801	2019-07-01 01:13:07.282371	\N	\N	100
state:AmnatCharoen:1906689	S2	Amnat Charoen	state	region:Northeastern:	1	499	2019-07-03 11:16:11.633285	\N	\N	100
state:Hampshire:2647554	S2	Hampshire	state	region:SouthEast:2637438	1	2017	2019-07-01 19:34:22.937378	\N	\N	100
state:KrongPailin:1830206	S2	Krong Pailin	state	country:Cambodia:1831722	1	998	2019-07-03 11:30:26.471712	\N	\N	100
state:Batdambang:1821310	S2	Batdâmbâng	state	country:Cambodia:1831722	1	1509	2019-07-03 11:10:55.383743	\N	\N	100
state:HaNoi:1581129	S2	Ha Noi	state	region:DongBac:11497248	1	502	2019-07-03 11:11:48.69762	\N	\N	100
state:Surkhandarya:	S2	Surkhandarya	state	country:Uzbekistan:1512440	1	2489	2019-07-01 15:29:40.924134	\N	\N	100
state:Masalli:147551	S2	Masallı	state	region:LankaranEconomicRegion:	1	495	2019-07-03 16:30:50.792515	\N	\N	100
state:Maryland:2275099	S2	Maryland	state	country:Liberia:2275384	1	1008	2019-06-30 22:58:06.831323	\N	\N	100
state:Manipur:1263706	S2	Manipur	state	region:Northeast:10575550	1	1211	2019-07-03 13:04:53.945426	\N	\N	100
state:BeauBassinRoseHill:934765	S2	Beau Bassin-Rose Hill	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.75332	\N	\N	100
state:Kunar:1135702	S2	Kunar	state	country:Afghanistan:1149361	1	993	2019-07-03 14:03:54.146508	\N	\N	100
state:IonioiNisoi:261774	S2	Ionioi Nisoi	state	country:Greece:390903	1	1015	2019-07-01 18:54:02.040362	\N	\N	100
state:Bərdə:587056	S2	Bərdə	state	region:AranEconomicRegion:	1	492	2019-07-03 16:33:16.713268	\N	\N	100
state:AlQalyubiyah:360621	S2	Al Qalyubiyah	state	country:Egypt:357994	1	519	2019-07-03 15:33:20.995161	\N	\N	100
state:Nicosia:146267	S2	Nicosia	state	country:Cyprus:146670	1	1043	2019-07-03 15:31:23.552246	\N	\N	100
state:Oslo:3143242	S2	Oslo	state	country:Norway:3144096	1	1901	2019-06-30 21:05:15.055707	\N	\N	100
state:ZuidHolland:2743698	S2	Zuid-Holland	state	country:Netherlands:2750405	1	1522	2019-06-30 20:54:20.817339	\N	\N	100
state:NamDinh:1905626	S2	Nam Định	state	region:DongBangSongHong:	1	504	2019-07-03 11:32:05.470722	\N	\N	100
state:Bongolava:7670853	S2	Bongolava	state	country:Madagascar:1062947	1	1188	2019-07-03 13:39:28.306833	\N	\N	100
state:KaohKong:1831037	S2	Kaôh Kong	state	country:Cambodia:1831722	1	1034	2019-07-03 11:30:33.40734	\N	\N	100
state:AlQahirah:360630	S2	Al Qahirah	state	country:Egypt:357994	1	535	2019-07-03 15:33:20.996628	\N	\N	100
country:Akrotiri:146839	S2	Akrotiri	country	continent:Asia:6255147	0	521	2019-07-03 15:31:23.550855	\N	\N	100
state:AlIsmaIliyah:361056	S2	Al Isma`iliyah	state	country:Egypt:357994	1	1034	2019-07-03 15:41:48.015578	\N	\N	100
state:PortLouisCity:934154	S2	Port Louis city	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.753877	\N	\N	100
region:East:1272123	S2	East	region	country:India:6269134	0	49721	2019-06-30 15:20:46.302736	\N	\N	100
state:Goycay:586482	S2	Göyçay	state	region:AranEconomicRegion:	1	991	2019-07-03 16:04:00.475674	\N	\N	100
state:Qəbələ:585738	S2	Qəbələ	state	region:ShakiZaqatalaEconomicRegion:	1	1009	2019-07-03 16:04:00.476182	\N	\N	100
country:Cyprus:146670	S2	Cyprus	country	continent:Asia:6255147	0	1044	2019-07-03 15:31:23.553588	\N	\N	100
state:Dubay:292224	S2	Dubay	state	country:UnitedArabEmirates:290557	1	504	2019-07-03 14:47:49.867644	\N	\N	100
state:AsSuways:359797	S2	As Suways	state	country:Egypt:357994	1	1064	2019-07-03 15:20:45.414007	\N	\N	100
state:Kagawa:1860834	S2	Kagawa	state	region:Shikoku:1852487	1	514	2019-07-03 08:38:00.733482	\N	\N	100
region:Corse:3023518	S2	Corse	region	country:France:3017382	0	3539	2019-07-02 01:24:34.690867	\N	\N	100
state:Trat:1605277	S2	Trat	state	region:Eastern:7114724	1	1003	2019-07-03 11:27:07.785228	\N	\N	100
state:Ismayilli:586320	S2	İsmayıllı	state	region:DaghligShirvanEconomicRegion:	1	1009	2019-07-03 16:04:00.477088	\N	\N	100
state:Musandam:411741	S2	Musandam	state	country:Oman:286963	1	998	2019-07-03 14:38:07.468908	\N	\N	100
state:Quba:585786	S2	Quba	state	region:GubaKhachmazEconomicRegion:	1	1085	2019-07-03 16:04:00.478196	\N	\N	100
state:NeutralZone:	S2	Neutral Zone	state	country:UnitedArabEmirates:290557	1	999	2019-07-03 14:47:49.865888	\N	\N	100
region:MaltaXlokk:	S2	Malta Xlokk	region	country:Malta:2562770	0	1028	2019-07-02 17:58:59.512827	\N	\N	100
state:Famagusta:146615	S2	Famagusta	state	country:Cyprus:146670	1	517	2019-07-03 15:32:25.898409	\N	\N	100
state:Firenze:3176958	S2	Firenze	state	region:Toscana:3165361	1	506	2019-07-03 17:43:57.363835	\N	\N	100
state:Strumitsa:863902	S2	Strumitsa	state	country:Macedonia:718075	1	508	2019-07-03 17:09:31.443143	\N	\N	100
state:LeKef:2473637	S2	Le Kef	state	country:Tunisia:2464461	1	997	2019-07-03 17:44:47.607955	\N	\N	100
state:Səmkir:585059	S2	Şəmkir	state	region:GanjaGazakhEconomicRegion:	1	493	2019-07-03 16:09:53.735482	\N	\N	100
state:Salyan:147269	S2	Salyan	state	region:AranEconomicRegion:	1	494	2019-07-03 16:30:50.793782	\N	\N	100
state:Lerik:147610	S2	Lerik	state	region:LankaranEconomicRegion:	1	1992	2019-06-30 20:01:17.519167	\N	\N	100
state:QuatreBornes:934131	S2	Quatre Bornes	state	country:Mauritius:934292	1	483	2019-07-03 13:42:31.754366	\N	\N	100
state:Cagliari:2525471	S2	Cagliari	state	region:Sardegna:2523228	1	1001	2019-07-03 17:48:18.022829	\N	\N	100
state:BurSaId:358617	S2	Bur Sa`id	state	country:Egypt:357994	1	516	2019-07-03 15:41:48.014819	\N	\N	100
state:Essonne:3019599	S2	Essonne	state	region:IleDeFrance:3012874	1	508	2019-07-03 20:02:41.133023	\N	\N	100
state:Zrnovci:862958	S2	Zrnovci	state	country:Macedonia:718075	1	509	2019-07-03 17:05:36.38811	\N	\N	100
state:Stockholm:2673722	S2	Stockholm	state	country:Sweden:2661886	1	4498	2019-06-30 18:18:07.52149	\N	\N	100
state:Dedza:930023	S2	Dedza	state	region:Central:931597	1	515	2019-07-03 16:29:36.636057	\N	\N	100
state:AddisAbaba:344979	S2	Addis Ababa	state	country:Ethiopia:337996	1	1001	2019-07-03 15:47:15.325439	\N	\N	100
country:Eswatini:934841	S2	eSwatini	country	continent:Africa:6255146	0	1038	2019-07-03 15:26:19.808605	\N	\N	100
state:Santander:3668578	S2	Santander	state	country:Colombia:3686110	1	5369	2019-07-01 01:14:09.222307	\N	\N	100
state:PointeNoire:2258738	S2	Pointe Noire	state	country:Congo:203312	1	502	2019-07-03 17:50:48.115907	\N	\N	100
state:Nuoro:3172153	S2	Nuoro	state	region:Sardegna:2523228	1	505	2019-07-03 17:59:50.695942	\N	\N	100
state:NkhataBay:924730	S2	Nkhata Bay	state	region:Northern:924591	1	1042	2019-07-03 16:27:56.622723	\N	\N	100
state:Chikwawa:931067	S2	Chikwawa	state	region:Southern:923817	1	1060	2019-07-03 16:27:30.311272	\N	\N	100
state:Tebessa:2477457	S2	Tébessa	state	country:Algeria:2589581	1	2034	2019-07-03 17:44:47.606733	\N	\N	100
state:HauteCorse:3013793	S2	Haute-Corse	state	region:Corse:3023518	1	3028	2019-07-02 01:24:34.690352	\N	\N	100
state:BioBio:3898380	S2	Bío-Bío	state	country:Chile:3895114	1	5713	2019-07-01 01:38:07.883321	\N	\N	100
state:LoirEtCher:2997856	S2	Loir-et-Cher	state	region:Centre:3027939	1	547	2019-07-03 19:37:32.3298	\N	\N	100
state:Tozeur:2464645	S2	Tozeur	state	country:Tunisia:2464461	1	1018	2019-07-03 17:48:59.370326	\N	\N	100
state:Plateaux:2364370	S2	Plateaux	state	country:Togo:2363686	1	1536	2019-07-03 19:39:13.2159	\N	\N	100
state:Vratsa:725713	S2	Vratsa	state	country:Bulgaria:732800	1	1027	2019-07-03 17:01:23.092883	\N	\N	100
state:Kasungu:928531	S2	Kasungu	state	region:Central:931597	1	525	2019-07-03 16:34:37.863062	\N	\N	100
state:Trieste:3165184	S2	Trieste	state	region:FriuliVeneziaGiulia:3176525	1	964	2019-06-30 18:02:41.801976	\N	\N	100
state:Nkhotakota:924703	S2	Nkhotakota	state	region:Central:931597	1	1032	2019-07-03 16:27:56.623754	\N	\N	100
state:Daskəsən:586771	S2	Daşkəsən	state	region:GanjaGazakhEconomicRegion:	1	492	2019-07-03 16:45:52.614544	\N	\N	100
state:Dabola:2422441	S2	Dabola	state	region:Faranah:8335086	1	979	2019-07-03 20:35:12.915823	\N	\N	100
state:MayoKebbiOuest:7603253	S2	Mayo-Kebbi Ouest	state	country:Chad:2434508	1	995	2019-07-03 18:32:59.296375	\N	\N	100
state:Ajman:292933	S2	Ajman	state	country:UnitedArabEmirates:290557	1	492	2019-07-03 14:55:43.722808	\N	\N	100
state:Nairobi:184742	S2	Nairobi	state	country:Kenya:192950	1	505	2019-07-03 17:25:28.609294	\N	\N	100
state:Agsu:587342	S2	Ağsu	state	region:DaghligShirvanEconomicRegion:	1	989	2019-07-03 16:39:25.895114	\N	\N	100
state:Ustecky:3339577	S2	Ústecký	state	country:CzechRepublic:3077311	1	1532	2019-07-03 17:51:45.526623	\N	\N	100
state:Cremona:3177837	S2	Cremona	state	region:Lombardia:3174618	1	519	2019-07-03 17:46:11.128742	\N	\N	100
state:NewryAndMourne:2641580	S2	Newry and Mourne	state	region:NorthernIreland:2635167	1	1522	2019-06-30 18:34:30.89055	\N	\N	100
state:SaintMichael:3373557	S2	Saint Michael	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.471405	\N	\N	100
state:Valverde:3492112	S2	Valverde	state	country:DominicanRepublic:3508796	1	1011	2019-07-03 21:53:25.043595	\N	\N	100
state:Faranah:8335086	S2	Faranah	state	region:Faranah:8335086	1	989	2019-07-03 20:33:37.040547	\N	\N	100
state:Belluno:3182209	S2	Belluno	state	region:Veneto:3164604	1	504	2019-07-03 18:02:28.630917	\N	\N	100
state:Koubia:2595302	S2	Koubia	state	region:Labe:8335089	1	1005	2019-07-03 20:13:47.313534	\N	\N	100
state:Tashkent:1484839	S2	Tashkent	state	country:Uzbekistan:1512440	1	1200	2019-07-01 15:25:43.85873	\N	\N	100
state:Jaen:2516394	S2	Jaén	state	region:Andalucia:2593109	1	1028	2019-07-03 19:44:24.466702	\N	\N	100
state:Lilongwe:927967	S2	Lilongwe	state	region:Central:931597	1	517	2019-07-03 16:26:25.453007	\N	\N	100
state:VilleDeNDjamena:7603254	S2	Ville de N'Djamena	state	country:Chad:2434508	1	1013	2019-07-03 18:28:16.585862	\N	\N	100
country:Afghanistan:1149361	S2	Afghanistan	country	continent:Asia:6255147	0	73619	2019-06-30 14:44:47.553899	\N	\N	100
state:Mellieha:8299733	S2	Mellieħa	state	region:MaltaMajjistral:	1	515	2019-07-02 17:58:59.513516	\N	\N	100
state:NorthAbaco:8030551	S2	North Abaco	state	country:Bahamas:3572887	1	503	2019-07-02 23:50:43.760874	\N	\N	100
state:DeuxSevres:3021501	S2	Deux-Sèvres	state	region:PoitouCharentes:11071620	1	510	2019-07-03 20:11:33.54732	\N	\N	100
state:Manica:1040947	S2	Manica	state	country:Mozambique:1036973	1	5362	2019-06-30 20:03:28.084506	\N	\N	100
state:Tunis:2464464	S2	Tunis	state	country:Tunisia:2464461	1	2018	2019-06-30 18:03:23.586852	\N	\N	100
state:Ferrara:3177088	S2	Ferrara	state	region:EmiliaRomagna:3177401	1	503	2019-07-03 18:03:09.283705	\N	\N	100
state:Eastern:2409543	S2	Eastern	state	country:SierraLeone:2403846	1	1489	2019-07-03 20:35:36.576453	\N	\N	100
state:PuertoPlata:3494267	S2	Puerto Plata	state	country:DominicanRepublic:3508796	1	1519	2019-07-03 21:45:04.520251	\N	\N	100
state:SaintJames:3373570	S2	Saint James	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.471865	\N	\N	100
state:Maritime:2365173	S2	Maritime	state	country:Togo:2363686	1	1002	2019-07-03 19:41:06.257517	\N	\N	100
state:Centre:2367237	S2	Centre	state	country:Togo:2363686	1	1557	2019-07-03 19:38:56.940846	\N	\N	100
state:Choiseul:3576794	S2	Choiseul	state	country:SaintLucia:3576468	1	498	2019-07-03 21:43:53.531479	\N	\N	100
state:SaltCay:3576939	S2	Salt Cay	state	country:TurksAndCaicosIslands:3576916	1	1008	2019-07-03 21:51:28.954924	\N	\N	100
state:Arima:3575052	S2	Arima	state	country:TrinidadAndTobago:3573591	1	497	2019-07-03 22:21:39.024845	\N	\N	100
state:Komen:3239078	S2	Komen	state	region:ObalnoKraska:	1	1011	2019-07-03 18:05:58.139917	\N	\N	100
state:Bosilovo:862946	S2	Bosilovo	state	country:Macedonia:718075	1	1019	2019-07-03 17:05:36.388896	\N	\N	100
state:Valcea:662892	S2	Vâlcea	state	country:Romania:798549	1	1024	2019-07-03 17:03:05.37172	\N	\N	100
state:Var:2970749	S2	Var	state	region:ProvenceAlpesCoteDAzur:2985244	1	1535	2019-07-02 01:36:43.815981	\N	\N	100
state:DemirKapija:862961	S2	Demir Kapija	state	region:Southeastern:9166199	1	475	2019-07-03 17:04:21.791688	\N	\N	100
state:Valandovo:784732	S2	Valandovo	state	region:Southeastern:9166199	1	508	2019-07-03 17:09:31.443665	\N	\N	100
state:Zou:2390719	S2	Zou	state	country:Benin:2395170	1	1989	2019-07-03 19:39:13.217328	\N	\N	100
state:Radovis:863890	S2	Radoviš	state	region:Eastern:9166195	1	509	2019-07-03 17:05:36.39076	\N	\N	100
state:GrandTurk:3576993	S2	Grand Turk	state	country:TurksAndCaicosIslands:3576916	1	1004	2019-07-03 21:51:28.955568	\N	\N	100
state:Neno:924963	S2	Neno	state	region:Southern:923817	1	516	2019-07-03 16:41:26.703308	\N	\N	100
state:Dinguiraye:2421902	S2	Dinguiraye	state	region:Faranah:8335086	1	1973	2019-07-03 19:42:58.545057	\N	\N	100
state:Niamey:2595294	S2	Niamey	state	country:Niger:2440476	1	2004	2019-07-03 19:46:33.983916	\N	\N	100
state:WestSussex:2634258	S2	West Sussex	state	region:SouthEast:2637438	1	507	2019-07-03 19:37:27.873058	\N	\N	100
state:Ntcheu:925072	S2	Ntcheu	state	region:Central:931597	1	518	2019-07-03 16:41:26.703817	\N	\N	100
state:VoreioAigaio:6697806	S2	Voreio Aigaio	state	country:Greece:390903	1	2044	2019-06-30 18:08:41.110322	\N	\N	100
state:Espaillat:3505867	S2	Espaillat	state	country:DominicanRepublic:3508796	1	507	2019-07-03 21:47:07.325021	\N	\N	100
state:Limavady:2644502	S2	Limavady	state	region:NorthernIreland:2635167	1	506	2019-07-03 19:58:18.521589	\N	\N	100
state:LaVega:3499977	S2	La Vega	state	country:DominicanRepublic:3508796	1	1030	2019-07-03 21:47:07.326196	\N	\N	100
state:SouthAbaco:8030555	S2	South Abaco	state	country:Bahamas:3572887	1	1492	2019-07-02 23:50:43.760357	\N	\N	100
state:Bomi:2278324	S2	Bomi	state	country:Liberia:2275384	1	979	2019-07-03 20:41:59.392267	\N	\N	100
state:Granada:2517115	S2	Granada	state	region:Andalucia:2593109	1	1032	2019-07-03 19:33:43.705069	\N	\N	100
state:Montserrado:2274890	S2	Montserrado	state	country:Liberia:2275384	1	492	2019-07-03 20:42:46.325339	\N	\N	100
state:Laborie:3576662	S2	Laborie	state	country:SaintLucia:3576468	1	498	2019-07-03 21:43:53.530323	\N	\N	100
state:Atlantique:2395504	S2	Atlantique	state	country:Benin:2395170	1	996	2019-07-03 19:41:06.259255	\N	\N	100
state:Madeira:2593105	S2	Madeira	state	country:Portugal:2264397	1	1002	2019-07-03 19:59:46.351307	\N	\N	100
state:Asuncion:3474570	S2	Asunción	state	country:Paraguay:3437598	1	2028	2019-07-02 03:58:09.01388	\N	\N	100
state:Atakora:2395524	S2	Atakora	state	country:Benin:2395170	1	2025	2019-07-03 19:37:09.751105	\N	\N	100
state:WesternTobago:3573591	S2	Western Tobago	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 21:44:17.194099	\N	\N	100
state:MiddleCaicos:3576964	S2	Middle Caicos	state	country:TurksAndCaicosIslands:3576916	1	1006	2019-07-03 21:49:41.853075	\N	\N	100
state:SanPedro:3437027	S2	San Pedro	state	country:Paraguay:3437598	1	972	2019-07-03 21:09:41.139178	\N	\N	100
state:Paraguari:3437599	S2	Paraguarí	state	country:Paraguay:3437598	1	1186	2019-07-03 21:16:02.942284	\N	\N	100
state:Cordillera:3438827	S2	Cordillera	state	country:Paraguay:3437598	1	854	2019-07-03 21:11:33.465315	\N	\N	100
state:MaeHongSon:1152221	S2	Mae Hong Son	state	region:Northern:6695803	1	2098	2019-06-30 10:15:06.13104	\N	\N	100
state:Amambay:3439433	S2	Amambay	state	country:Paraguay:3437598	1	1324	2019-07-03 21:02:53.148115	\N	\N	100
state:Mendoza:3844419	S2	Mendoza	state	country:Argentina:3865483	1	17760	2019-07-01 01:19:41.160424	\N	\N	100
state:SaintLucy:3373565	S2	Saint Lucy	state	country:Barbados:3374084	1	997	2019-07-03 22:09:43.470961	\N	\N	100
channel:CanalDoSul:3411121	S2	Canal do Sul	channel	root	1	1365	2019-07-03 20:00:41.715356	\N	\N	100
state:Ascension:2411430	S2	Ascension	state	country:SaintHelena:3370751	1	491	2019-07-03 21:28:21.805903	\N	\N	100
state:Donga:2597274	S2	Donga	state	country:Benin:2395170	1	1028	2019-07-03 19:45:59.608826	\N	\N	100
bay:BocaGrande:8894067	S2	Boca Grande	bay	root	1	1008	2019-07-03 21:55:08.275949	\N	\N	100
state:CiudadDeBuenosAires:3433955	S2	Ciudad de Buenos Aires	state	country:Argentina:3865483	1	504	2019-07-03 21:20:04.296011	\N	\N	100
state:Guaira:3438049	S2	Guairá	state	country:Paraguay:3437598	1	169	2019-07-03 21:16:02.942776	\N	\N	100
state:Limerick:2962941	S2	Limerick	state	region:MidWest:	1	486	2019-07-03 20:09:11.413881	\N	\N	100
state:SaintPatrick:3577818	S2	Saint Patrick	state	country:SaintVincentAndTheGrenadines:3577815	1	499	2019-07-03 22:23:42.908713	\N	\N	100
state:ChristChurch:3373988	S2	Christ Church	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.472779	\N	\N	100
state:Gueckedou:2420561	S2	Guéckédou	state	region:Nzerekore:8335091	1	506	2019-07-03 20:35:36.577474	\N	\N	100
state:NordEst:3719540	S2	Nord-Est	state	country:Haiti:3723988	1	1015	2019-07-03 21:53:25.043069	\N	\N	100
state:Kayah:1319539	S2	Kayah	state	country:Myanmar:1327865	1	1544	2019-06-30 10:15:06.13376	\N	\N	100
state:Longford:2962839	S2	Longford	state	region:Midlands:	1	472	2019-07-03 20:15:09.199354	\N	\N	100
state:PortOfSpain:3573890	S2	Port of Spain	state	country:TrinidadAndTobago:3573591	1	497	2019-07-03 22:21:39.025353	\N	\N	100
year:2019	S2	2019	year	root	1	2305118	2019-06-30 09:51:38.324839	\N	\N	100
state:Micoud:3576567	S2	Micoud	state	country:SaintLucia:3576468	1	498	2019-07-03 21:43:53.531047	\N	\N	100
state:SanFernando:3573739	S2	San Fernando	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.025844	\N	\N	100
state:PointFortin:7521943	S2	Point Fortin	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.026325	\N	\N	100
state:Chaguanas:7521937	S2	Chaguanas	state	country:TrinidadAndTobago:3573591	1	497	2019-07-03 22:21:39.026786	\N	\N	100
state:SantiagoRodriguez:3492912	S2	Santiago Rodríguez	state	country:DominicanRepublic:3508796	1	1016	2019-07-03 21:53:25.044931	\N	\N	100
state:Santiago:3492918	S2	Santiago	state	country:DominicanRepublic:3508796	1	1011	2019-07-03 21:47:07.327018	\N	\N	100
state:PrincesTown:7521942	S2	Princes Town	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.030473	\N	\N	100
state:CiudadDeLaHabana:3564073	S2	Ciudad de la Habana	state	country:Cuba:3562981	1	507	2019-07-04 00:28:01.159852	\N	\N	100
state:SudEst:3716950	S2	Sud-Est	state	country:Haiti:3723988	1	1020	2019-07-04 00:24:49.939193	\N	\N	100
state:IlleEtVilaine:3012849	S2	Ille-et-Vilaine	state	region:Bretagne:3030293	1	2050	2019-07-01 19:13:17.165389	\N	\N	100
state:NuevaSegovia:3617458	S2	Nueva Segovia	state	country:Nicaragua:3617476	1	1012	2019-07-04 01:17:02.944182	\N	\N	100
state:Artemisa:7668824	S2	Artemisa	state	country:Cuba:3562981	1	516	2019-07-04 00:28:01.160417	\N	\N	100
state:SaintJoseph:3373568	S2	Saint Joseph	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.457279	\N	\N	100
state:Guanacaste:3623582	S2	Guanacaste	state	country:CostaRica:3624060	1	1037	2019-07-04 01:17:06.374697	\N	\N	100
state:Somme:2974304	S2	Somme	state	region:Picardie:11071624	1	1533	2019-06-30 21:12:28.116439	\N	\N	100
region:WestMidlands:11591953	S2	West Midlands	region	country:UnitedKingdom:2635167	0	1635	2019-07-01 19:42:13.758162	\N	\N	100
country:HeardIslandAndMcdonaldIslands:1547314	S2	Heard Island and McDonald Islands	country	continent:SevenSeas(OpenOcean):	0	2919	2019-06-30 13:42:23.178901	\N	\N	100
state:SaintAndrew:3577822	S2	Saint Andrew	state	country:SaintVincentAndTheGrenadines:3577815	1	501	2019-07-03 22:23:42.907704	\N	\N	100
state:Azua:3512209	S2	Azua	state	country:DominicanRepublic:3508796	1	1020	2019-07-04 00:44:44.314453	\N	\N	100
state:Huila:3680692	S2	Huila	state	country:Colombia:3686110	1	1568	2019-07-04 00:31:43.807595	\N	\N	100
state:Bogota:3688685	S2	Bogota	state	country:Colombia:3686110	1	676	2019-07-04 00:32:37.450862	\N	\N	100
state:PinarDelRio:3544088	S2	Pinar del Río	state	country:Cuba:3562981	1	2533	2019-07-03 23:18:10.936463	\N	\N	100
state:Masaya:3617722	S2	Masaya	state	country:Nicaragua:3617476	1	1012	2019-07-04 01:02:58.880376	\N	\N	100
state:Saarland:2842635	S2	Saarland	state	country:Germany:2921044	1	2071	2019-06-30 09:51:55.691197	\N	\N	100
state:DeltaAmacuro:3644541	S2	Delta Amacuro	state	country:Venezuela:3625428	1	4039	2019-07-03 21:58:18.348742	\N	\N	100
state:SaintJohn:3373569	S2	Saint John	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.469504	\N	\N	100
state:Boaco:3620673	S2	Boaco	state	country:Nicaragua:3617476	1	1013	2019-07-04 01:04:36.905775	\N	\N	100
state:Grenadines:3577892	S2	Grenadines	state	country:SaintVincentAndTheGrenadines:3577815	1	509	2019-07-03 22:23:42.908234	\N	\N	100
state:HauteVienne:3013719	S2	Haute-Vienne	state	region:Limousin:11071620	1	2047	2019-06-30 21:05:12.574002	\N	\N	100
state:Carazo:3620481	S2	Carazo	state	country:Nicaragua:3617476	1	1015	2019-07-04 01:02:58.880899	\N	\N	100
state:BasSassandra:2597326	S2	Bas-Sassandra	state	country:IvoryCoast:2287781	1	2056	2019-07-02 19:29:50.42347	\N	\N	100
state:Ghanzi:933758	S2	Ghanzi	state	country:Botswana:933860	1	13494	2019-06-30 15:53:41.903762	\N	\N	100
state:DiegoMartin:7521939	S2	Diego Martin	state	country:TrinidadAndTobago:3573591	1	497	2019-07-03 22:21:39.027268	\N	\N	100
state:Wrexham:2633484	S2	Wrexham	state	region:WestWalesAndTheValleys:	1	998	2019-07-02 19:38:22.622894	\N	\N	100
state:Cesar:3686880	S2	Cesar	state	country:Colombia:3686110	1	1602	2019-07-04 00:28:42.942831	\N	\N	100
state:SanJuanLaventille:7521946	S2	San Juan-Laventille	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.027748	\N	\N	100
state:IslaDeLaJuventud:3556608	S2	Isla de la Juventud	state	country:Cuba:3562981	1	1995	2019-07-03 23:29:57.350729	\N	\N	100
state:SaintGeorge:3577819	S2	Saint George	state	country:SaintVincentAndTheGrenadines:3577815	1	502	2019-07-03 22:23:42.909149	\N	\N	100
state:PenalDebe:7521941	S2	Penal-Debe	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.028193	\N	\N	100
state:SaintDavid:3577821	S2	Saint David	state	country:SaintVincentAndTheGrenadines:3577815	1	499	2019-07-03 22:23:42.909607	\N	\N	100
state:Tunapuna/Piarco:7521947	S2	Tunapuna/Piarco	state	country:TrinidadAndTobago:3573591	1	498	2019-07-03 22:21:39.02911	\N	\N	100
state:Bobonaro:1648513	S2	Bobonaro	state	country:TimorLeste:1966436	1	1012	2019-06-30 10:33:26.683216	\N	\N	100
state:SaintThomas:3373551	S2	Saint Thomas	state	country:Barbados:3374084	1	999	2019-07-03 22:09:43.470024	\N	\N	100
state:Chontales:3620368	S2	Chontales	state	country:Nicaragua:3617476	1	1031	2019-07-04 01:04:36.906474	\N	\N	100
state:CheshireEast:7290536	S2	Cheshire East	state	region:NorthWest:2641227	1	1522	2019-07-01 19:34:46.984675	\N	\N	100
state:Quindio:3671087	S2	Quindío	state	country:Colombia:3686110	1	1024	2019-07-04 00:31:12.943825	\N	\N	100
state:Tolima:3666951	S2	Tolima	state	country:Colombia:3686110	1	2746	2019-07-04 00:31:12.944416	\N	\N	100
state:SantoDomingoDeLosTsachilas:7062136	S2	Santo Domingo de los Tsáchilas	state	country:Ecuador:3658394	1	323	2019-07-02 08:24:53.288858	\N	\N	100
state:Managua:3617762	S2	Managua	state	country:Nicaragua:3617476	1	514	2019-07-04 01:02:58.881774	\N	\N	100
state:RioClaroMayaro:3574155	S2	Rio Claro-Mayaro	state	country:TrinidadAndTobago:3573591	1	996	2019-07-03 22:21:39.029997	\N	\N	100
state:GrandGedeh:2276622	S2	Grand Gedeh	state	country:Liberia:2275384	1	1523	2019-06-30 22:25:52.788348	\N	\N	100
state:Derbyshire:2651346	S2	Derbyshire	state	region:EastMidlands:11591952	1	2029	2019-07-01 19:34:46.985694	\N	\N	100
state:Rangpur:7671048	S2	Rangpur	state	country:Bangladesh:1210997	1	1030	2019-07-04 10:20:09.885881	\N	\N	100
state:Granada:3619135	S2	Granada	state	country:Nicaragua:3617476	1	1016	2019-07-04 01:04:36.905045	\N	\N	100
state:Hermanas:3493282	S2	Hermanas	state	country:DominicanRepublic:3508796	1	506	2019-07-03 21:47:07.324152	\N	\N	100
state:OumElBouaghi:2484618	S2	Oum el Bouaghi	state	country:Algeria:2589581	1	1964	2019-07-02 01:42:36.354389	\N	\N	100
state:UbonRatchathani:1906688	S2	Ubon Ratchathani	state	region:Northeastern:	1	2563	2019-06-30 12:42:27.590194	\N	\N	100
state:Daga:1337280	S2	Daga	state	region:Central:	1	502	2019-07-04 10:34:21.020262	\N	\N	100
state:UthaiThani:1149965	S2	Uthai Thani	state	region:Central:10177180	1	702	2019-07-04 11:23:34.495429	\N	\N	100
state:Diana:7670842	S2	Diana	state	country:Madagascar:1062947	1	4211	2019-06-30 15:32:27.565546	\N	\N	100
state:Macau:1821274	S2	Macau	state	country:Macao:1821274	1	499	2019-07-04 09:47:30.27587	\N	\N	100
state:Enga:2097655	S2	Enga	state	country:PapuaNewGuinea:2088628	1	2515	2019-06-30 20:08:30.755843	\N	\N	100
state:Chhukha:1337279	S2	Chhukha	state	region:Western:	1	505	2019-07-04 10:34:21.020803	\N	\N	100
state:Sud:2140464	S2	Sud	state	country:NewCaledonia:2139685	1	756	2019-07-04 04:59:08.576661	\N	\N	100
state:Angaur:4037653	S2	Angaur	state	country:Palau:1559581	1	976	2019-07-04 08:28:58.575879	\N	\N	100
state:Gasa:7303651	S2	Gasa	state	region:Central:	1	993	2019-07-04 10:35:52.20093	\N	\N	100
state:TaiPo:1818672	S2	Tai Po	state	region:TheNewTerritories:	1	513	2019-07-04 09:58:11.982615	\N	\N	100
state:Moray:2642240	S2	Moray	state	region:HighlandsAndIslands:6621347	1	106	2019-12-27 14:17:11.405569	\N	\N	100
state:FesBoulemane:2548882	S2	Fès - Boulemane	state	country:Morocco:2542007	1	3715	2019-06-30 22:24:15.406126	\N	\N	100
country:Macao:1821274	S2	Macao	country	continent:Asia:6255147	0	499	2019-07-04 09:47:30.276418	\N	\N	100
region:PoitouCharentes:11071620	S2	Poitou-Charentes	region	country:France:3017382	0	4414	2019-06-30 21:13:08.586952	\N	\N	100
state:Samchi:1337289	S2	Samchi	state	region:Western:	1	502	2019-07-04 10:42:59.565397	\N	\N	100
state:Ha:1337283	S2	Ha	state	region:Western:	1	499	2019-07-04 10:37:16.284784	\N	\N	100
state:Cantal:3028791	S2	Cantal	state	region:Auvergne:11071625	1	1365	2019-06-30 21:10:54.071582	\N	\N	100
state:Tongsa:1337294	S2	Tongsa	state	region:Southern:	1	411	2019-07-04 10:41:11.183004	\N	\N	100
state:Kayangel:1559774	S2	Kayangel	state	country:Palau:1559581	1	975	2019-07-04 08:27:19.933119	\N	\N	100
state:Thimphu:1337293	S2	Thimphu	state	region:Western:	1	497	2019-07-04 10:21:02.587267	\N	\N	100
state:Phayao:1607758	S2	Phayao	state	region:Northern:6695803	1	528	2019-07-04 11:27:08.721501	\N	\N	100
state:ChiangRai:1153668	S2	Chiang Rai	state	region:Northern:6695803	1	719	2019-07-04 11:47:41.883029	\N	\N	100
state:Kweneng:933562	S2	Kweneng	state	country:Botswana:933860	1	6601	2019-06-30 20:09:24.792673	\N	\N	100
state:Indre:3012805	S2	Indre	state	region:Centre:3027939	1	1544	2019-06-30 21:13:35.570138	\N	\N	100
state:SaintPeter:3373554	S2	Saint Peter	state	country:Barbados:3374084	1	997	2019-07-03 22:09:43.457785	\N	\N	100
state:Northern:2199295	S2	Northern	state	country:Fiji:2205218	1	1484	2019-07-04 05:57:29.497054	\N	\N	100
state:YuenLong:1818224	S2	Yuen Long	state	region:TheNewTerritories:	1	500	2019-07-04 09:41:21.423305	\N	\N	100
state:Uppsala:2666218	S2	Uppsala	state	country:Sweden:2661886	1	2879	2019-07-02 01:42:47.319339	\N	\N	100
state:Rucavas:7628302	S2	Rucavas	state	region:Kurzeme:460496	1	4	2019-12-29 13:56:04.316008	\N	\N	100
state:Phangnga:1151462	S2	Phangnga	state	region:Southern:10234924	1	530	2019-07-04 11:30:03.342758	\N	\N	100
state:Torba:2137421	S2	Torba	state	country:Vanuatu:2134431	1	504	2019-07-04 05:24:47.795032	\N	\N	100
state:Vienne:2969280	S2	Vienne	state	region:PoitouCharentes:11071620	1	2067	2019-06-30 21:13:35.571432	\N	\N	100
region:Western:	S2	Western	region	country:Bhutan:1252634	0	1013	2019-07-04 10:21:02.588065	\N	\N	100
state:EastSepik:2097846	S2	East Sepik	state	country:PapuaNewGuinea:2088628	1	6491	2019-06-30 20:06:32.700904	\N	\N	100
state:Aveyron:3035691	S2	Aveyron	state	region:MidiPyrenees:11071623	1	1589	2019-06-30 20:53:17.335711	\N	\N	100
state:Bokeo:1904616	S2	Bokeo	state	country:Laos:1655842	1	562	2019-07-04 12:10:08.044698	\N	\N	100
state:Islands:1819708	S2	Islands	state	region:TheNewTerritories:	1	501	2019-07-04 09:47:30.276909	\N	\N	100
state:Ngarchelong:4038037	S2	Ngarchelong	state	country:Palau:1559581	1	489	2019-07-04 08:28:40.261103	\N	\N	100
state:Valparaiso:3868621	S2	Valparaíso	state	country:Chile:3895114	1	2078	2019-07-04 00:36:32.730782	\N	\N	100
state:BinhThuan:1581882	S2	Bình Thuận	state	region:DongNamBo:11497301	1	1517	2019-07-02 12:15:45.72551	\N	\N	100
state:Banten:1923045	S2	Banten	state	country:Indonesia:1643084	1	1505	2019-07-04 09:36:28.835511	\N	\N	100
state:Kalangala:11259109	S2	Kalangala	state	region:Central:234594	1	510	2019-07-04 14:28:03.228436	\N	\N	100
state:Penama:2208266	S2	Penama	state	country:Vanuatu:2134431	1	1514	2019-07-04 05:16:58.864347	\N	\N	100
state:Kumi:230584	S2	Kumi	state	region:Eastern:8260673	1	460	2019-07-04 14:47:49.113578	\N	\N	100
state:Alebtong:235293	S2	Alebtong	state	region:Northern:8260674	1	502	2019-07-04 14:33:01.09374	\N	\N	100
state:Sembabule:226361	S2	Sembabule	state	region:Central:234594	1	437	2019-07-04 14:27:43.533699	\N	\N	100
state:Francistown:933778	S2	Francistown	state	country:Botswana:933860	1	159	2019-07-04 14:18:57.945542	\N	\N	100
state:GrandAnsePraslin:241331	S2	Grand'Anse Praslin	state	country:Seychelles:241170	1	463	2019-07-04 11:10:17.431374	\N	\N	100
state:LamDong:1577882	S2	Lâm Đồng	state	region:TayNguyen:11497253	1	2015	2019-07-02 12:16:19.060356	\N	\N	100
state:Assam:1278253	S2	Assam	state	region:Northeast:10575550	1	8304	2019-06-30 17:00:03.914955	\N	\N	100
state:Masindi:229362	S2	Masindi	state	region:Western:8260675	1	515	2019-07-04 14:30:04.660202	\N	\N	100
state:Mukono:228853	S2	Mukono	state	region:Central:234594	1	502	2019-07-04 14:41:51.680011	\N	\N	100
country:SpratlyIslands:1821005	S2	Spratly Islands	country	continent:Asia:6255147	0	1479	2019-07-03 10:29:22.070779	\N	\N	100
state:IndreEtLoire:3012804	S2	Indre-et-Loire	state	region:Centre:3027939	1	2041	2019-06-30 21:12:54.520263	\N	\N	100
state:Peleliu:4038261	S2	Peleliu	state	country:Palau:1559581	1	489	2019-07-04 08:28:58.57646	\N	\N	100
state:Tlemcen:2475683	S2	Tlemcen	state	country:Algeria:2589581	1	1035	2019-06-30 22:42:35.363276	\N	\N	100
state:BaieSainteAnne:241438	S2	Baie Sainte Anne	state	country:Seychelles:241170	1	463	2019-07-04 11:10:17.432164	\N	\N	100
state:Karbala:94823	S2	Karbala'	state	region:Iraq:99237	1	494	2019-07-03 16:32:38.92251	\N	\N	100
state:Mayuge:229292	S2	Mayuge	state	region:Eastern:8260673	1	1007	2019-07-04 14:35:35.136611	\N	\N	100
state:Rakai:226727	S2	Rakai	state	region:Central:234594	1	663	2019-07-04 14:28:07.180009	\N	\N	100
state:Lampang:1152472	S2	Lampang	state	region:Northern:6695803	1	695	2019-07-04 11:33:43.497066	\N	\N	100
state:MoreOgRomsdal:3145495	S2	Møre og Romsdal	state	country:Norway:3144096	1	3577	2019-06-30 21:06:48.470552	\N	\N	100
state:Auckland:2193734	S2	Auckland	state	region:NorthIsland:2185983	1	500	2019-07-04 11:53:56.132672	\N	\N	100
state:JakartaRaya:1642907	S2	Jakarta Raya	state	country:Indonesia:1643084	1	1000	2019-07-04 09:37:37.661645	\N	\N	100
state:Goa:1271157	S2	Goa	state	region:West:1252881	1	1042	2019-07-04 12:43:48.089475	\N	\N	100
state:Amuria:235186	S2	Amuria	state	region:Eastern:8260673	1	504	2019-07-04 14:33:01.094731	\N	\N	100
state:LesMamelles:448408	S2	Les Mamelles	state	country:Seychelles:241170	1	644	2019-07-04 11:30:56.154887	\N	\N	100
state:Limburg:2751596	S2	Limburg	state	country:Netherlands:2750405	1	3072	2019-06-30 21:05:06.705123	\N	\N	100
state:Sanma:2134898	S2	Sanma	state	country:Vanuatu:2134431	1	1527	2019-07-04 05:13:40.064044	\N	\N	100
state:DongNamBo:11497301	S2	Đông Nam Bộ	state	country:Vietnam:1562822	1	1017	2019-07-02 12:16:19.060846	\N	\N	100
state:Malampa:2208265	S2	Malampa	state	country:Vanuatu:2134431	1	1521	2019-07-04 04:58:14.827305	\N	\N	100
state:Ranong:1150964	S2	Ranong	state	region:Southern:10234924	1	518	2019-07-04 11:25:02.250731	\N	\N	100
state:ChaiNat:1611415	S2	Chai Nat	state	region:Central:10177180	1	527	2019-07-04 11:43:15.569631	\N	\N	100
state:AlKhawr:389465	S2	Al Khawr	state	country:Qatar:289688	1	161	2019-07-04 14:53:19.463494	\N	\N	100
state:Banaadir:64833	S2	Banaadir	state	country:Somalia:51537	1	156	2019-07-04 13:59:10.492438	\N	\N	100
state:Amolatar:235203	S2	Amolatar	state	region:Northern:8260674	1	155	2019-07-04 14:51:08.689458	\N	\N	100
state:Buvuma:8030576	S2	Buvuma	state	region:Central:234594	1	1011	2019-07-04 14:35:35.138042	\N	\N	100
state:Macvanski:7581799	S2	Macvanski	state	region:Macvanski:7581799	1	1018	2019-07-01 18:58:33.641587	\N	\N	100
state:Moselle:2991627	S2	Moselle	state	region:Lorraine:11071622	1	2067	2019-06-30 09:51:55.693071	\N	\N	100
state:Taranto:3165924	S2	Taranto	state	region:Apulia:3169778	1	520	2019-07-04 16:32:41.745707	\N	\N	100
state:Posavina:3343706	S2	Posavina	state	region:RepuplikaSrpska:	1	520	2019-07-04 16:15:10.121048	\N	\N	100
state:Sukhothai:1150532	S2	Sukhothai	state	region:Central:10177180	1	539	2019-07-04 11:33:43.498139	\N	\N	100
state:Samaxi:585030	S2	Şamaxı	state	region:DaghligShirvanEconomicRegion:	1	2003	2019-06-30 19:55:09.527625	\N	\N	100
state:Portalegre:2264507	S2	Portalegre	state	region:Alentejo:2268252	1	676	2019-07-04 18:18:16.766763	\N	\N	100
state:BasseKotto:240396	S2	Basse-Kotto	state	country:CentralAfricanRepublic:239880	1	2190	2019-07-04 16:40:35.137964	\N	\N	100
state:CasteloBranco:2269513	S2	Castelo Branco	state	region:Centro:11886822	1	516	2019-07-04 18:19:35.008842	\N	\N	100
state:SariPul:1127110	S2	Sari Pul	state	country:Afghanistan:1149361	1	2020	2019-07-04 11:51:47.72518	\N	\N	100
state:BaselStadt:2661602	S2	Basel-Stadt	state	country:Switzerland:2658434	1	514	2019-07-04 18:56:44.65008	\N	\N	100
state:Ziro:2597269	S2	Ziro	state	region:CentreOuest:6930707	1	545	2019-07-04 20:03:20.528609	\N	\N	100
state:Andjazidja:921882	S2	Andjazîdja	state	country:Comoros:921929	1	1027	2019-07-04 15:22:59.224105	\N	\N	100
state:Guarda:2738782	S2	Guarda	state	region:Centro:11886822	1	509	2019-07-04 18:24:20.295847	\N	\N	100
state:Lamwo:230211	S2	Lamwo	state	region:Northern:8260674	1	1003	2019-07-04 14:30:02.279713	\N	\N	100
state:Eastern:4036647	S2	Eastern	state	country:Fiji:2205218	1	1479	2019-07-04 11:36:07.040157	\N	\N	100
state:Durres:3344947	S2	Durrës	state	country:Albania:783754	1	2039	2019-07-01 18:30:28.9441	\N	\N	100
state:Cetinje:3202640	S2	Cetinje	state	country:Montenegro:3194884	1	2036	2019-07-01 18:26:56.603673	\N	\N	100
state:Sedhiou:2249781	S2	Sédhiou	state	country:Senegal:2245662	1	536	2019-07-04 17:55:14.390211	\N	\N	100
state:BarlettaAndriaTrani:6955699	S2	Barletta-Andria Trani	state	region:Apulia:3169778	1	517	2019-07-04 16:28:34.00731	\N	\N	100
state:RocheCaiman:448409	S2	Roche Caïman	state	country:Seychelles:241170	1	672	2019-07-04 11:30:56.155408	\N	\N	100
state:Liege:2792414	S2	Liege	state	region:Walloon:3337387	1	2546	2019-06-30 21:06:37.852448	\N	\N	100
state:Fife:2649469	S2	Fife	state	region:Eastern:2650458	1	499	2019-07-04 17:35:21.981439	\N	\N	100
state:Saatli:147287	S2	Saatlı	state	region:AranEconomicRegion:	1	999	2019-06-30 20:04:38.649833	\N	\N	100
state:Lima:3936452	S2	Lima	state	country:Peru:3932488	1	5771	2019-07-01 02:16:39.533014	\N	\N	100
state:TangerTetouan:2597556	S2	Tanger - Tétouan	state	country:Morocco:2542007	1	4616	2019-07-01 19:36:34.230606	\N	\N	100
state:Limburg:2792349	S2	Limburg	state	region:Flemish:3337388	1	1016	2019-06-30 21:10:48.016872	\N	\N	100
state:Ulcinj:3188514	S2	Ulcinj	state	country:Montenegro:3194884	1	1020	2019-07-01 18:30:28.942333	\N	\N	100
state:Kitgum:230893	S2	Kitgum	state	region:Northern:8260674	1	158	2019-07-04 14:34:58.453799	\N	\N	100
state:Amuru:8015635	S2	Amuru	state	region:Northern:8260674	1	505	2019-07-04 14:39:00.066466	\N	\N	100
state:Madaba:443123	S2	Madaba	state	country:Jordan:248816	1	1029	2019-06-30 19:14:13.424141	\N	\N	100
state:Buyende:8657773	S2	Buyende	state	region:Eastern:8260673	1	155	2019-07-04 14:51:08.688818	\N	\N	100
state:Nakasongola:228418	S2	Nakasongola	state	region:Central:234594	1	155	2019-07-04 14:51:08.690686	\N	\N	100
state:PointeLaRue:241221	S2	Pointe La Rue	state	country:Seychelles:241170	1	485	2019-07-04 11:30:56.155871	\N	\N	100
state:Bilecik:321122	S2	Bilecik	state	country:Turkey:298795	1	1036	2019-07-04 15:12:34.974612	\N	\N	100
state:Lezhe:3344949	S2	Lezhë	state	country:Albania:783754	1	1525	2019-07-01 18:30:28.944573	\N	\N	100
state:AlDaayen:8030540	S2	Al Daayen	state	country:Qatar:289688	1	168	2019-07-04 14:53:19.462621	\N	\N	100
state:UmmSalal:389467	S2	Umm Salal	state	country:Qatar:289688	1	168	2019-07-04 14:53:19.462112	\N	\N	100
state:Lobatse:933521	S2	Lobatse	state	country:Botswana:933860	1	491	2019-07-04 14:14:17.572107	\N	\N	100
state:Gaborone:7670702	S2	Gaborone	state	country:Botswana:933860	1	490	2019-07-04 14:14:17.572635	\N	\N	100
state:CentralBosnia:3343731	S2	Central Bosnia	state	region:FederacijaBosnaIHercegovina:	1	528	2019-07-04 16:18:36.169324	\N	\N	100
state:Siggiewi:8299754	S2	Siġġiewi	state	region:MaltaMajjistral:	1	513	2019-07-04 16:20:24.587038	\N	\N	100
state:Kiryandongo:8657902	S2	Kiryandongo	state	region:Western:8260675	1	1009	2019-07-04 14:33:42.005371	\N	\N	100
state:Apac:235130	S2	Apac	state	region:Northern:8260674	1	504	2019-07-04 14:33:42.006043	\N	\N	100
country:Greece:390903	S2	Greece	country	continent:Europe:6255148	0	25013	2019-06-30 17:59:35.781299	\N	\N	100
state:BruneiAndMuara:1820818	S2	Brunei and Muara	state	country:Brunei:1820814	1	499	2019-07-03 10:52:16.78609	\N	\N	100
state:Phuket:1151253	S2	Phuket	state	region:Southern:10234924	1	529	2019-07-04 11:26:15.894392	\N	\N	100
state:Fars:134766	S2	Fars	state	country:Iran:130758	1	14894	2019-07-01 19:17:00.161673	\N	\N	100
sea:AlboranSea:6640412	S2	Alboran Sea	sea	root	1	7589	2019-06-30 22:17:33.729969	\N	\N	100
state:Cacheu:2374312	S2	Cacheu	state	region:Norte:2373280	1	514	2019-07-04 17:56:30.515984	\N	\N	100
state:KamphaengPhet:1153089	S2	Kamphaeng Phet	state	region:Central:10177180	1	547	2019-07-04 11:44:37.446206	\N	\N	100
state:Nayala:2597265	S2	Nayala	state	region:BoucleDuMouhoun:6930701	1	527	2019-07-04 20:15:19.333434	\N	\N	100
state:Grevenmacher:2960513	S2	Grevenmacher	state	country:Luxembourg:2960313	1	4100	2019-06-30 09:51:55.690214	\N	\N	100
state:Sanguie:2355930	S2	Sanguié	state	region:CentreOuest:6930707	1	521	2019-07-04 20:15:19.334153	\N	\N	100
state:NorthWest:933230	S2	North-West	state	country:Botswana:933860	1	16359	2019-06-30 15:39:47.257307	\N	\N	100
state:Melilla:6362988	S2	Melilla	state	region:Melilla:7577101	1	2037	2019-06-30 23:02:05.13566	\N	\N	100
state:HauteRhin:3013663	S2	Haute-Rhin	state	region:Alsace:11071622	1	540	2019-07-04 18:56:44.653669	\N	\N	100
state:Sucre:3667725	S2	Sucre	state	country:Colombia:3686110	1	3214	2019-07-02 08:22:54.344935	\N	\N	100
state:Houet:2360075	S2	Houet	state	region:HautsBassins:6930710	1	1071	2019-07-04 19:25:53.603798	\N	\N	100
state:Hamah:170015	S2	Hamah	state	country:Syria:163843	1	2561	2019-06-30 19:10:37.275935	\N	\N	100
state:Hordaland:3151864	S2	Hordaland	state	country:Norway:3144096	1	2905	2019-06-30 21:05:22.899029	\N	\N	100
state:Limassol:146383	S2	Limassol	state	country:Cyprus:146670	1	1033	2019-07-03 15:31:23.552716	\N	\N	100
state:Balqa:250751	S2	Balqa	state	country:Jordan:248816	1	516	2019-06-30 19:16:04.297963	\N	\N	100
state:TierraDelFuego:3834450	S2	Tierra del Fuego	state	country:Argentina:3865483	1	3463	2019-07-02 04:55:05.206163	\N	\N	100
state:Caqueta:3687479	S2	Caquetá	state	country:Colombia:3686110	1	9127	2019-07-01 01:12:48.114894	\N	\N	100
state:GharbChrardaBeniHssen:	S2	Gharb - Chrarda - Béni Hssen	state	country:Morocco:2542007	1	2545	2019-07-01 19:13:22.163988	\N	\N	100
state:TiziOuzou:2475741	S2	Tizi Ouzou	state	country:Algeria:2589581	1	673	2019-07-04 18:51:33.726401	\N	\N	100
state:Ceredigion:2653814	S2	Ceredigion	state	region:EastWales:7302126	1	10	2019-07-04 18:13:03.974218	\N	\N	100
country:Montserrat:3578097	S2	Montserrat	country	continent:NorthAmerica:6255149	0	514	2019-07-02 00:09:40.298437	\N	\N	100
state:Nordland:3144301	S2	Nordland	state	country:Norway:3144096	1	11530	2019-06-30 21:05:48.80035	\N	\N	100
region:MidEast:	S2	Mid-East	region	country:Ireland:2963597	0	2039	2019-06-30 21:35:30.603194	\N	\N	100
state:ZanzibarSouthAndCentral:148728	S2	Zanzibar South and Central	state	country:Tanzania:149590	1	668	2019-06-30 20:46:42.447027	\N	\N	100
state:Boumerdes:2502638	S2	Boumerdès	state	country:Algeria:2589581	1	167	2019-07-04 19:06:16.487245	\N	\N	100
state:Ragusa:2523649	S2	Ragusa	state	region:Sicily:2523119	1	1047	2019-07-02 17:52:19.084166	\N	\N	100
country:Vanuatu:2134431	S2	Vanuatu	country	continent:Oceania:6255151	0	1039	2019-07-04 05:13:40.064548	\N	\N	100
state:Lusaka:909129	S2	Lusaka	state	country:Zambia:895949	1	3752	2019-07-04 13:49:22.266166	\N	\N	100
state:Lasko:3196759	S2	Laško	state	region:Savinjska:3186550	1	6	2019-07-05 17:54:47.106869	\N	\N	100
state:Kamnik:3198364	S2	Kamnik	state	region:Osrednjeslovenska:	1	3	2019-07-05 16:22:37.337878	\N	\N	100
state:Radece:3239189	S2	Radece	state	region:Savinjska:3186550	1	2	2019-07-05 17:55:43.521626	\N	\N	100
state:Kilinochchi:1240371	S2	Kilinŏchchi	state	region:UturuPalata:	1	5	2020-01-06 08:58:11.398514	\N	\N	100
state:Veles:863909	S2	Veles	state	region:Vardar:833492	1	3	2019-10-21 15:01:42.093719	\N	\N	100
state:Larnaca:146398	S2	Larnaca	state	country:Cyprus:146670	1	517	2019-07-03 15:32:25.899269	\N	\N	100
state:Analanjirofo:7670848	S2	Analanjirofo	state	country:Madagascar:1062947	1	2678	2019-06-30 15:30:38.790604	\N	\N	100
lagoon:Waddenzee:3230283	S2	Waddenzee	lagoon	root	1	1525	2019-06-30 21:10:25.479811	\N	\N	100
state:Passore:2356667	S2	Passoré	state	region:Nord:6930706	1	521	2019-07-04 20:10:27.250822	\N	\N	100
state:Marne:2995603	S2	Marne	state	region:ChampagneArdenne:11071622	1	2057	2019-06-30 21:05:31.559438	\N	\N	100
state:Kutahya:305267	S2	Kütahya	state	country:Turkey:298795	1	1579	2019-07-04 15:13:58.223408	\N	\N	100
state:WestFlanders:2783770	S2	West Flanders	state	region:Flemish:3337388	1	1264	2019-06-30 21:10:03.549052	\N	\N	100
state:PasDeCalais:2988430	S2	Pas-de-Calais	state	region:NordPasDeCalais:11071624	1	2271	2019-06-30 21:10:03.549486	\N	\N	100
state:Oppland:3143487	S2	Oppland	state	country:Norway:3144096	1	4743	2019-06-30 21:10:26.693178	\N	\N	100
state:NongKhai:1608231	S2	Nong Khai	state	region:Northeastern:	1	245	2019-07-08 15:02:15.132197	\N	\N	100
state:AndamanAndNicobar:1278647	S2	Andaman and Nicobar	state	country:India:6269134	1	1041	2019-06-30 17:05:09.357159	\N	\N	100
region:BasseNormandie:3034693	S2	Basse-Normandie	region	country:France:3017382	0	4224	2019-07-01 19:50:27.176533	\N	\N	100
state:BaRiaVungTau:1584534	S2	Bà Rịa - Vũng Tàu	state	region:DongNamBo:11497301	1	506	2019-07-02 12:35:07.616782	\N	\N	100
state:NZiComoe:2597331	S2	N'zi-Comoé	state	country:IvoryCoast:2287781	1	1193	2019-06-30 09:51:44.472002	\N	\N	100
state:Connecticut:4831725	S2	Connecticut	state	region:Northeast:11887749	1	556	2019-07-03 01:43:19.924345	\N	\N	100
state:Ivanovo:555235	S2	Ivanovo	state	region:Central:11961322	1	4116	2019-06-30 19:07:13.250397	\N	\N	100
state:NorthSolomons:2089470	S2	North Solomons	state	country:PapuaNewGuinea:2088628	1	2487	2019-06-30 18:47:04.949946	\N	\N	100
state:Kildare:2963435	S2	Kildare	state	region:MidEast:	1	8	2019-10-23 20:02:34.831067	\N	\N	100
state:Zulia:3625035	S2	Zulia	state	country:Venezuela:3625428	1	6844	2019-07-01 01:13:31.176285	\N	\N	100
region:Central:234594	S2	Central	region	country:Uganda:226074	0	5237	2019-07-02 16:22:49.74389	\N	\N	100
region:IleDeFrance:3012874	S2	Île-de-France	region	country:France:3017382	0	2038	2019-06-30 21:09:45.402225	\N	\N	100
state:Valencia:2509951	S2	Valencia	state	region:Valenciana:2593113	1	1052	2019-06-30 21:31:08.453915	\N	\N	100
state:Huesca:3120513	S2	Huesca	state	region:Aragon:3336899	1	3575	2019-06-30 21:07:57.914912	\N	\N	100
region:Flemish:3337388	S2	Flemish	region	country:Belgium:2802361	0	2319	2019-06-30 21:09:19.72789	\N	\N	100
state:Essex:2649889	S2	Essex	state	region:East:2637438	1	2025	2019-07-01 19:37:33.856569	\N	\N	100
state:LosRios:3654592	S2	Los Rios	state	country:Ecuador:3658394	1	679	2019-07-02 08:24:25.025061	\N	\N	100
state:Sofia:7670847	S2	Sofia	state	country:Madagascar:1062947	1	9433	2019-06-30 15:30:54.70059	\N	\N	100
region:East:2637438	S2	East	region	country:UnitedKingdom:2635167	0	6433	2019-07-01 19:30:44.322423	\N	\N	100
state:Baki:587081	S2	Bakı	state	region:AbsheronEconomicRegion:	1	501	2019-06-30 20:09:19.604704	\N	\N	100
state:Havering:3333153	S2	Havering	state	region:GreaterLondon:2648110	1	1015	2019-07-01 19:49:37.138641	\N	\N	100
country:PuertoRico:4566966	S2	Puerto Rico	country	continent:NorthAmerica:6255149	0	1048	2019-07-02 23:36:58.063116	\N	\N	100
state:Kebili:2468014	S2	Kebili	state	country:Tunisia:2464461	1	4045	2019-06-30 18:08:23.592974	\N	\N	100
state:MacquarieIsland:2159161	S2	Macquarie Island	state	country:Australia:2077456	1	893	2019-07-16 02:55:37.956184	\N	\N	100
country:Uruguay:3439705	S2	Uruguay	country	continent:SouthAmerica:6255150	0	20962	2019-06-30 22:28:19.966668	\N	\N	100
state:Rayong:1607016	S2	Rayong	state	region:Eastern:7114724	1	4	2019-07-06 14:20:54.282688	\N	\N	100
country:Luxembourg:2960313	S2	Luxembourg	country	continent:Europe:6255148	0	1032	2019-07-02 19:18:50.69967	\N	\N	100
state:Loiret:2997857	S2	Loiret	state	region:Centre:3027939	1	1034	2019-06-30 21:12:47.488374	\N	\N	100
region:Southeastern:9166199	S2	Southeastern	region	country:Macedonia:718075	0	1018	2019-07-03 17:05:36.392183	\N	\N	100
state:KuyavianPomeranian:3337500	S2	Kuyavian-Pomeranian	state	country:Poland:798544	1	6715	2019-06-30 18:01:06.691876	\N	\N	100
region:Centre:3027939	S2	Centre	region	country:France:3017382	0	9268	2019-06-30 20:54:54.800625	\N	\N	100
state:CaymanIslands:3580718	S2	Cayman Islands	state	country:CaymanIslands:3580718	1	1023	2019-07-01 03:03:52.870155	\N	\N	100
state:Cauca:3687029	S2	Cauca	state	country:Colombia:3686110	1	5229	2019-07-02 08:28:49.669318	\N	\N	100
state:GornjiPetrovci:3239275	S2	Gornji Petrovci	state	region:Pomurska:	1	2	2019-07-07 14:07:18.244934	\N	\N	100
state:Hodos:3344940	S2	Hodoš	state	region:Pomurska:	1	2	2019-07-07 14:11:49.530891	\N	\N	100
state:MeurtheEtMoselle:2994111	S2	Meurthe-et-Moselle	state	region:Lorraine:11071622	1	25	2019-07-07 17:16:51.542758	\N	\N	100
state:ZamboangaSibugay:7115981	S2	Zamboanga Sibugay	state	region:ZamboangaPeninsula(RegionIx):	1	2	2019-10-22 07:19:35.268323	\N	\N	100
state:DemirHisar:863846	S2	Demir Hisar	state	region:Southwestern:9072896	1	8	2019-07-16 15:23:33.24929	\N	\N	100
state:Copperbelt:917524	S2	Copperbelt	state	country:Zambia:895949	1	3737	2019-07-02 17:08:04.44545	\N	\N	100
state:Masaka:229380	S2	Masaka	state	region:Central:234594	1	1	2019-07-14 13:08:14.895551	\N	\N	100
state:IslandHarbour:11205441	S2	Island Harbour	state	country:Anguilla:3573512	1	181	2019-07-06 21:38:23.708132	\N	\N	100
state:Neamt:672460	S2	Neamt	state	country:Romania:798549	1	2064	2019-06-30 19:01:31.714207	\N	\N	100
state:Aisne:3038375	S2	Aisne	state	region:Picardie:11071624	1	1539	2019-06-30 21:12:16.869499	\N	\N	100
state:SidiBelAbbes:2481001	S2	Sidi Bel Abbès	state	country:Algeria:2589581	1	3143	2019-06-30 22:42:35.3625	\N	\N	100
state:Roscommon:2961731	S2	Roscommon	state	region:West:8642922	1	12	2019-10-23 19:30:20.880015	\N	\N	100
region:Cataluna:3336901	S2	Cataluña	region	country:Spain:2510769	0	5389	2019-06-30 21:06:25.36725	\N	\N	100
state:NorthWestern:900594	S2	North-Western	state	country:Zambia:895949	1	12881	2019-06-30 15:39:20.052901	\N	\N	100
state:BenArous(TunisSud):2472477	S2	Ben Arous (Tunis Sud)	state	country:Tunisia:2464461	1	2012	2019-06-30 18:03:23.58729	\N	\N	100
state:Zaghouan:2464038	S2	Zaghouan	state	country:Tunisia:2464461	1	2016	2019-06-30 18:03:23.58811	\N	\N	100
day:04	S2	04	day	root	1	942505	2019-07-04 04:55:57.441744	\N	\N	100
state:Potosi:3907580	S2	Potosí	state	country:Bolivia:3923057	1	16147	2019-07-01 01:19:19.942898	\N	\N	100
state:Bitola:863486	S2	Bitola	state	region:Pelagonia:	1	8	2019-07-16 16:06:42.149502	\N	\N	100
state:Struga:863901	S2	Struga	state	region:Southwestern:9072896	1	2	2019-07-16 15:23:16.22526	\N	\N	100
state:Central:3439137	S2	Central	state	country:Paraguay:3437598	1	1527	2019-07-02 03:58:09.014387	\N	\N	100
state:Montana:453753	S2	Montana	state	country:Bulgaria:732800	1	1025	2019-07-03 17:00:47.531106	\N	\N	100
state:TuamotuGambier:4030621	S2	Tuamotu-Gambier	state	country:FrenchPolynesia:4030656	1	464	2019-10-24 00:37:36.477984	\N	\N	100
state:MariaTrinidadSanchez:3496772	S2	María Trinidad Sánchez	state	country:DominicanRepublic:3508796	1	2050	2019-06-30 23:42:03.569883	\N	\N	100
state:Southampton:3573026	S2	Southampton	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.366149	\N	\N	100
state:Baleares:6424360	S2	Baleares	state	region:IslasBaleares:2521383	1	3285	2019-07-02 19:09:48.446456	\N	\N	100
state:Allier:3038111	S2	Allier	state	region:Auvergne:11071625	1	1815	2019-06-30 21:13:24.665042	\N	\N	100
state:Pargaujas:7628373	S2	Pargaujas	state	region:Vidzeme:7639661	1	14	2019-07-26 14:50:10.604835	\N	\N	100
state:DongThap:9781231	S2	Ðong Tháp	state	region:DongBangSongCuuLong:1574717	1	6	2019-07-20 10:25:22.31748	\N	\N	100
state:Fujayrah:292879	S2	Fujayrah	state	country:UnitedArabEmirates:290557	1	1475	2019-07-03 14:38:07.4699	\N	\N	100
state:Otuke:8030567	S2	Otuke	state	region:Northern:8260674	1	4	2019-08-03 12:52:36.055969	\N	\N	100
state:Karsavas:7628351	S2	Karsavas	state	region:Latgale:7639662	1	4	2019-07-26 14:50:05.008419	\N	\N	100
state:Krimuldas:7628361	S2	Krimuldas	state	region:Riga:456173	1	1	2019-07-26 14:50:10.604338	\N	\N	100
state:Kilkenny:2963397	S2	Kilkenny	state	region:SouthEast:	1	1	2019-07-25 20:50:07.706577	\N	\N	100
state:CesinovoOblesevo:862977	S2	Češinovo-Obleševo	state	region:Eastern:9166195	1	94	2019-08-02 14:57:56.769595	\N	\N	100
state:Delcevo:863844	S2	Delčevo	state	region:Eastern:9166195	1	2	2019-08-02 14:53:09.724144	\N	\N	100
state:SvetiNikole:863905	S2	Sveti Nikole	state	region:Northeastern:9072895	1	2	2019-08-02 16:53:05.541754	\N	\N	100
state:Aube:3036420	S2	Aube	state	region:ChampagneArdenne:11071622	1	1528	2019-06-30 21:06:58.23168	\N	\N	100
state:Tarragona:3108287	S2	Tarragona	state	region:Cataluna:3336901	1	2032	2019-06-30 21:11:22.433867	\N	\N	100
state:Carchi:3659718	S2	Carchi	state	country:Ecuador:3658394	1	671	2019-07-02 08:22:41.244174	\N	\N	100
state:GaziBaba:863853	S2	Gazi Baba	state	region:GreaterSkopje:	1	5	2019-08-02 14:57:56.768634	\N	\N	100
state:Pembroke:3573095	S2	Pembroke	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.363668	\N	\N	100
state:Saravan:1653333	S2	Saravan	state	country:Laos:1655842	1	1018	2019-06-30 12:41:37.677919	\N	\N	100
state:GrandCapeMount:2276627	S2	Grand Cape Mount	state	country:Liberia:2275384	1	986	2019-07-03 20:41:59.392721	\N	\N	100
state:NakhonPathom:1608533	S2	Nakhon Pathom	state	region:Central:10177180	1	23	2019-07-21 11:02:20.274756	\N	\N	100
state:Aitutaki:4036508	S2	Aitutaki	state	country:CookIslands:1899402	1	912	2019-10-24 23:13:03.207156	\N	\N	100
state:Alicante:2521976	S2	Alicante	state	region:Valenciana:2593113	1	1529	2019-06-30 21:14:07.794928	\N	\N	100
region:IslasBaleares:2521383	S2	Islas Baleares	region	country:Spain:2510769	0	3290	2019-07-02 19:09:48.447035	\N	\N	100
state:BordjBouArreridj:2503699	S2	Bordj Bou Arréridj	state	country:Algeria:2589581	1	2023	2019-06-30 09:51:53.940025	\N	\N	100
state:Quneitra:173336	S2	Quneitra	state	country:Syria:163843	1	1019	2019-06-30 19:05:32.931035	\N	\N	100
state:Mayenne:2994932	S2	Mayenne	state	region:PaysDeLaLoire:2988289	1	1539	2019-07-01 19:13:40.037605	\N	\N	100
gulf:GulfOfMasira:287294	S2	Gulf of Masira	gulf	root	1	2053	2019-06-30 15:54:39.653854	\N	\N	100
state:GradSofiya:731061	S2	Grad Sofiya	state	country:Bulgaria:732800	1	1015	2019-07-03 17:01:40.889856	\N	\N	100
state:Kanem:2430873	S2	Kanem	state	country:Chad:2434508	1	7553	2019-07-01 19:38:13.067181	\N	\N	100
state:CarriacouAndPetiteMartinique:7303836	S2	Carriacou and Petite Martinique	state	country:Grenada:3580239	1	500	2019-07-03 22:25:29.360459	\N	\N	100
region:Valenciana:2593113	S2	Valenciana	region	country:Spain:2510769	0	4100	2019-06-30 21:05:47.949835	\N	\N	100
state:SmithS:3573031	S2	Smith's	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.364595	\N	\N	100
state:Pernik:728331	S2	Pernik	state	country:Bulgaria:732800	1	646	2019-07-03 17:01:40.890288	\N	\N	100
state:Nabeul:2468576	S2	Nabeul	state	country:Tunisia:2464461	1	511	2019-06-30 18:03:23.588504	\N	\N	100
state:Murcia:2513413	S2	Murcia	state	region:Murcia:6355234	1	2567	2019-06-30 22:42:23.5642	\N	\N	100
region:BasnahiraPalata:	S2	Basnāhira paḷāta	region	country:SriLanka:1227603	0	1035	2019-06-30 15:22:56.027089	\N	\N	100
region:Murcia:6355234	S2	Murcia	region	country:Spain:2510769	0	2567	2019-06-30 22:42:23.564653	\N	\N	100
state:Sandys:3573050	S2	Sandys	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.365051	\N	\N	100
state:Paget:3573103	S2	Paget	state	country:Bermuda:3573345	1	281	2019-10-23 21:16:47.365485	\N	\N	100
state:Oyam:7056295	S2	Oyam	state	region:Northern:8260674	1	2	2019-08-03 12:45:05.804383	\N	\N	100
state:Nagano:1856210	S2	Nagano	state	region:Chubu:1864496	1	4155	2019-06-30 10:14:13.09445	\N	\N	100
state:Relizane:2483666	S2	Relizane	state	country:Algeria:2589581	1	1043	2019-07-02 18:48:15.255205	\N	\N	100
state:Sonsorol:1559630	S2	Sonsorol	state	country:Palau:1559581	1	147	2019-08-11 04:34:48.188521	\N	\N	100
state:Bar:3204508	S2	Bar	state	country:Montenegro:3194884	1	1021	2019-07-01 18:30:28.943633	\N	\N	100
region:Labe:8335089	S2	Labé	region	country:Guinea:2420477	0	5007	2019-07-01 20:59:05.31734	\N	\N	100
region:Bourgogne:11071619	S2	Bourgogne	region	country:France:3017382	0	4719	2019-06-30 21:10:57.984074	\N	\N	100
state:PomeroonSupenaam:3379023	S2	Pomeroon-Supenaam	state	country:Guyana:3378535	1	5093	2019-06-30 23:42:50.892314	\N	\N	100
state:SouthLanarkshire:3333236	S2	South Lanarkshire	state	region:SouthWestern:2637295	1	7	2019-08-08 16:22:02.181623	\N	\N	100
bay:VincennesBay:6627575	S2	Vincennes Bay	bay	root	1	1017	2019-08-25 09:51:19.741889	\N	\N	100
state:ValDOise:2971071	S2	Val-d'Oise	state	region:IleDeFrance:3012874	1	5	2019-08-02 17:28:55.230649	\N	\N	100
state:Phichit:1607724	S2	Phichit	state	region:Central:10177180	1	23	2019-08-13 10:09:23.733604	\N	\N	100
state:MambereKadei:2386161	S2	Mambéré-Kadéï	state	country:CentralAfricanRepublic:239880	1	3166	2019-06-30 18:35:45.716985	\N	\N	100
state:Ampara:1251460	S2	Ampāra	state	region:NaĕGenahiraPalata:	1	1024	2019-07-02 12:38:24.048846	\N	\N	100
state:Sofia:727012	S2	Sofia	state	country:Bulgaria:732800	1	1029	2019-07-03 17:01:40.890676	\N	\N	100
state:Poni:2356386	S2	Poni	state	region:SudOuest:6930714	1	527	2019-06-30 09:51:47.463034	\N	\N	100
state:Mauke:4035646	S2	Mauke	state	country:CookIslands:1899402	1	453	2019-10-24 23:13:33.519625	\N	\N	100
state:Pontevedra:3113208	S2	Pontevedra	state	region:Galicia:3336902	1	10	2019-11-09 15:34:15.229319	\N	\N	100
state:Ogliastra:6457403	S2	Ogliastra	state	region:Sardegna:2523228	1	7	2019-08-02 16:11:13.46753	\N	\N	100
state:Pembrokeshire:2640500	S2	Pembrokeshire	state	region:EastWales:7302126	1	2000	2019-07-02 19:28:24.378166	\N	\N	100
state:Wakiso:225964	S2	Wakiso	state	region:Central:234594	1	1	2019-08-18 15:15:24.662951	\N	\N	100
state:Busia:234077	S2	Busia	state	region:Eastern:8260673	1	1	2019-08-18 15:01:49.356409	\N	\N	100
state:Lipkovo:863875	S2	Lipkovo	state	region:Skopje:785841	1	7	2019-08-02 14:57:56.770482	\N	\N	100
state:MountainProvince:1699175	S2	Mountain Province	state	region:CordilleraAdministrativeRegion(Car):	1	12	2019-08-16 11:03:16.859066	\N	\N	100
region:Khartum:379253	S2	Khartum	region	country:Sudan:366755	0	2376	2019-06-30 16:09:31.717965	\N	\N	100
state:Gulu:233346	S2	Gulu	state	region:Northern:8260674	1	11	2019-12-06 12:12:17.663791	\N	\N	100
state:Jinja:233114	S2	Jinja	state	region:Eastern:8260673	1	1	2019-08-18 14:42:25.09157	\N	\N	100
state:Ajdovscina:3204853	S2	Ajdovščina	state	region:Goriska:8988273	1	1	2019-11-15 18:51:23.676137	\N	\N	100
state:Mangochi:927245	S2	Mangochi	state	region:Southern:923817	1	1551	2019-07-03 16:29:36.635277	\N	\N	100
state:Centre:3728069	S2	Centre	state	country:Haiti:3723988	1	1523	2019-07-04 00:30:36.626928	\N	\N	100
state:Dordogne:3021042	S2	Dordogne	state	region:Aquitaine:11071620	1	3072	2019-06-30 20:46:51.809186	\N	\N	100
state:Loroum:2597264	S2	Loroum	state	region:Nord:6930706	1	557	2019-06-30 09:51:48.686543	\N	\N	100
region:PaysDeLaLoire:2988289	S2	Pays de la Loire	region	country:France:3017382	0	6367	2019-06-30 21:09:02.007877	\N	\N	100
state:Balaka:931865	S2	Balaka	state	region:Southern:923817	1	4	2019-08-12 14:05:33.369007	\N	\N	100
state:Pedernales:3495136	S2	Pedernales	state	country:DominicanRepublic:3508796	1	2031	2019-07-04 00:27:13.286093	\N	\N	100
state:Modena:3173330	S2	Modena	state	region:EmiliaRomagna:3177401	1	17	2019-11-03 14:07:52.116876	\N	\N	100
state:Sahbuz:147212	S2	Şahbuz	state	region:NaxcivanAutonomousRepublic:	1	3	2019-08-15 12:46:35.467652	\N	\N	100
state:SeineEtMarne:2975249	S2	Seine-et-Marne	state	region:IleDeFrance:3012874	1	2017	2019-06-30 21:09:45.401586	\N	\N	100
state:Messina:2524169	S2	Messina	state	region:Sicily:2523119	1	2061	2019-07-02 17:54:49.16559	\N	\N	100
state:CovaLima:1639462	S2	Cova Lima	state	country:TimorLeste:1966436	1	506	2019-06-30 10:33:26.68384	\N	\N	100
state:Bataan:1726347	S2	Bataan	state	region:CentralLuzon(RegionIii):	1	28	2019-08-11 09:52:04.144557	\N	\N	100
state:WestDunbartonshire:3333237	S2	West Dunbartonshire	state	region:SouthWestern:2637295	1	28	2019-08-09 15:13:34.198891	\N	\N	100
state:Ziguinchor:2243939	S2	Ziguinchor	state	country:Senegal:2245662	1	1530	2019-07-02 20:19:27.836661	\N	\N	100
state:ElTarf:2497322	S2	El Tarf	state	country:Algeria:2589581	1	1993	2019-07-02 01:31:50.089093	\N	\N	100
state:Yonne:2967222	S2	Yonne	state	region:Bourgogne:11071619	1	1043	2019-06-30 21:17:02.936532	\N	\N	100
bay:BahiaBlanca:3865084	S2	Bahía Blanca	bay	root	1	2064	2019-07-02 11:39:48.81249	\N	\N	100
state:TarnEtGaronne:2973357	S2	Tarn-et-Garonne	state	region:MidiPyrenees:11071623	1	37	2019-08-19 18:32:40.27353	\N	\N	100
state:SemenawiKeyihBahri:448497	S2	Semenawi Keyih Bahri	state	country:Eritrea:338010	1	3776	2019-07-01 16:13:54.03112	\N	\N	100
state:Jendouba:2470085	S2	Jendouba	state	country:Tunisia:2464461	1	497	2019-07-03 17:48:34.468008	\N	\N	100
state:Hatobohei:1559776	S2	Hatobohei	state	country:Palau:1559581	1	294	2019-08-11 04:33:03.452185	\N	\N	100
state:Serravalle:3166650	S2	Serravalle	state	country:SanMarino:3345302	1	1017	2019-06-30 18:01:32.859568	\N	\N	100
state:Mitiaro:4035642	S2	Mitiaro	state	country:CookIslands:1899402	1	453	2019-10-24 23:13:33.65006	\N	\N	100
state:Faafu:1282393	S2	Faafu	state	region:Central:8030594	1	22	2019-08-30 11:05:12.479475	\N	\N	100
state:Lot:2997524	S2	Lot	state	region:MidiPyrenees:11071623	1	513	2019-06-30 20:46:51.810866	\N	\N	100
state:Bafata:2375255	S2	Bafatá	state	region:Leste:2371768	1	9	2019-08-28 18:12:17.080642	\N	\N	100
country:Congo:2260494	S2	Congo	country	continent:Africa:6255146	0	33016	2019-06-30 18:08:39.316346	\N	\N	100
state:Jinotega:3618928	S2	Jinotega	state	country:Nicaragua:3617476	1	1513	2019-07-04 01:14:18.108169	\N	\N	100
state:Inhambane:1045110	S2	Inhambane	state	country:Mozambique:1036973	1	7455	2019-06-30 19:49:26.549352	\N	\N	100
state:SocTrang:1559972	S2	Sóc Trăng	state	region:DongBangSongCuuLong:1574717	1	89	2019-09-08 08:16:54.971353	\N	\N	100
state:Bengo:2243598	S2	Bengo	state	country:Angola:3351879	1	6635	2019-06-30 18:19:09.238477	\N	\N	100
region:Auvergne:11071625	S2	Auvergne	region	country:France:3017382	0	5406	2019-06-30 21:10:54.072391	\N	\N	100
state:Rivas:3617051	S2	Rivas	state	country:Nicaragua:3617476	1	509	2019-07-04 01:15:52.936765	\N	\N	100
state:Braga:2742031	S2	Braga	state	region:Norte:3372843	1	10	2019-09-05 16:50:43.40583	\N	\N	100
state:StocktonOnTees:3333203	S2	Stockton-on-Tees	state	region:NorthEast:11591950	1	2	2019-08-28 17:58:41.365391	\N	\N	100
strait:BransfieldStrait:6629641	S2	Bransfield Strait	strait	root	1	5812	2019-08-26 19:02:56.080871	\N	\N	100
state:Mangaia:4035667	S2	Mangaia	state	country:CookIslands:1899402	1	908	2019-10-24 23:13:57.159594	\N	\N	100
region:Picardie:11071624	S2	Picardie	region	country:France:3017382	0	4110	2019-06-30 21:09:45.403342	\N	\N	100
state:ChariBaguirmi:2434478	S2	Chari-Baguirmi	state	country:Chad:2434508	1	4792	2019-06-30 17:02:19.762915	\N	\N	100
state:HaiPhong:1581297	S2	Hải Phòng	state	region:DongBangSongHong:	1	11	2019-08-19 09:09:40.009539	\N	\N	100
state:OmbellaMPoko:2383765	S2	Ombella-M'Poko	state	country:CentralAfricanRepublic:239880	1	4576	2019-06-30 18:19:48.891753	\N	\N	100
region:Kanto:1860102	S2	Kanto	region	country:Japan:1861060	0	4221	2019-06-30 10:10:42.687782	\N	\N	100
sea:ScotiaSea:3426293	S2	Scotia Sea	sea	root	1	852	2019-08-26 19:02:31.851751	\N	\N	100
state:Moravicki:7581800	S2	Moravicki	state	region:Moravicki:7581800	1	1022	2019-07-01 18:52:42.496731	\N	\N	100
state:Groningen:2755249	S2	Groningen	state	country:Netherlands:2750405	1	4069	2019-06-30 21:05:55.098398	\N	\N	100
state:Namentenga:2357611	S2	Namentenga	state	region:CentreNord:6930706	1	22	2019-08-20 17:21:25.739702	\N	\N	100
state:Ibanda:7056284	S2	Ibanda	state	region:Western:8260675	1	14	2019-11-09 14:23:46.903075	\N	\N	100
sound:GeorgeViSound:6633775	S2	George VI Sound	sound	root	1	4620	2019-09-05 19:42:29.176053	\N	\N	100
state:Oise:2989663	S2	Oise	state	region:Picardie:11071624	1	1037	2019-06-30 21:09:45.402792	\N	\N	100
test	*	test	hashtag	root	1	1	2019-11-13 14:06:14.658527	\N	\N	100
state:RiverNile:408664	S2	River Nile	state	region:Northern:408667	1	15652	2019-06-30 16:10:13.217332	\N	\N	100
coolstuff	*	coolstuff	hashtag	root	1	1	2019-11-13 14:06:14.659914	\N	\N	100
state:Bogdanci:862950	S2	Bogdanci	state	region:Southeastern:9166199	1	2	2019-08-22 14:58:19.132357	\N	\N	100
state:Carlow:2965767	S2	Carlow	state	region:SouthEast:	1	3	2019-08-16 15:10:31.857038	\N	\N	100
state:Pavilostas:7628300	S2	Pavilostas	state	region:Kurzeme:460496	1	11	2019-08-24 16:20:02.045556	\N	\N	100
state:Phrae:1607551	S2	Phrae	state	region:Northern:6695803	1	14	2019-08-18 12:14:11.881875	\N	\N	100
state:Nauksenu:7628369	S2	Nauksenu	state	region:Vidzeme:7639661	1	4	2019-08-16 16:12:56.841807	\N	\N	100
state:Luweero:8581570	S2	Luweero	state	region:Central:234594	1	2	2019-08-18 14:24:11.123413	\N	\N	100
state:Wiltshire:2633868	S2	Wiltshire	state	region:SouthWest:11591956	1	10	2019-08-30 15:40:46.193446	\N	\N	100
state:Matagalpa:3617707	S2	Matagalpa	state	country:Nicaragua:3617476	1	1012	2019-07-04 01:14:18.108807	\N	\N	100
state:Kabarole:233476	S2	Kabarole	state	region:Western:8260675	1	10	2019-11-09 14:23:46.766101	\N	\N	100
state:PuyDeDome:2984986	S2	Puy-de-Dôme	state	region:Auvergne:11071625	1	1700	2019-06-30 21:17:49.849255	\N	\N	100
state:PyreneesOrientales:2984885	S2	Pyrénées-Orientales	state	region:LanguedocRoussillon:11071623	1	47	2019-08-21 13:16:33.124839	\N	\N	100
state:Strabane:11353073	S2	Strabane	state	region:NorthernIreland:2635167	1	19	2019-11-15 18:59:47.117934	\N	\N	100
state:RiverGee:2593120	S2	River Gee	state	country:Liberia:2275384	1	2020	2019-06-30 22:28:08.827325	\N	\N	100
gulf:WrigleyGulf:6628301	S2	Wrigley Gulf	gulf	root	1	1779	2019-09-10 23:28:47.858516	\N	\N	100
state:KrivaPalanka:863869	S2	Kriva Palanka	state	region:Northeastern:9072895	1	1010	2019-07-01 18:31:00.863432	\N	\N	100
state:Atiu:11695126	S2	Atiu	state	country:CookIslands:1899402	1	455	2019-10-24 23:14:21.513421	\N	\N	100
state:NoordBrabant:2749990	S2	Noord-Brabant	state	country:Netherlands:2750405	1	1031	2019-06-30 21:14:17.023258	\N	\N	100
country:Netherlands:2750405	S2	Netherlands	country	continent:Europe:6255148	0	9293	2019-06-30 20:54:20.817755	\N	\N	100
fjord:Trondheimsfjorden:3133878	S2	Trondheimsfjorden	fjord	root	1	1273	2019-06-30 21:12:08.928106	\N	\N	100
state:Takev:1821939	S2	Takêv	state	country:Cambodia:1831722	1	996	2019-06-30 12:43:14.973286	\N	\N	100
state:Kuldigas:458459	S2	Kuldigas	state	region:Kurzeme:460496	1	10	2019-09-17 14:41:16.961144	\N	\N	100
state:SangreGrande:7521944	S2	Sangre Grande	state	country:TrinidadAndTobago:3573591	1	1494	2019-07-03 21:44:17.195271	\N	\N	100
state:Mandoul:7603252	S2	Mandoul	state	country:Chad:2434508	1	2259	2019-06-30 18:40:34.505114	\N	\N	100
state:Delaware:4142224	S2	Delaware	state	region:South:11887752	1	14	2019-09-08 21:41:35.578452	\N	\N	100
state:Doubs:3020989	S2	Doubs	state	region:FrancheComte:11071619	1	2060	2019-07-02 19:04:31.618745	\N	\N	100
sea:AmundsenSea:4036629	S2	Amundsen Sea	sea	root	1	7854	2019-09-06 22:12:18.207239	\N	\N	100
state:Bouira:2502951	S2	Bouira	state	country:Algeria:2589581	1	670	2019-06-30 09:51:53.433086	\N	\N	100
state:EasternTobago:7521940	S2	Eastern Tobago	state	country:TrinidadAndTobago:3573591	1	500	2019-07-03 21:44:17.194709	\N	\N	100
sound:McmurdoSound:6637890	S2	McMurdo Sound	sound	root	1	5238	2019-09-09 00:23:34.238807	\N	\N	100
state:Jura:7285004	S2	Jura	state	country:Switzerland:2658434	1	1029	2019-07-04 18:56:44.652166	\N	\N	100
state::	S2	_all	state	country:Colombia:3686110	1	111	2022-06-06 22:30:21.340864	\N	\N	100
state:Mono:2392716	S2	Mono	state	country:Benin:2395170	1	499	2019-07-03 19:41:06.259693	\N	\N	100
bay:MargueriteBay:6066240	S2	Marguerite Bay	bay	root	1	2379	2019-08-31 20:12:12.194343	\N	\N	100
bay:LutzowHolmBay:6637274	S2	Lützow-Holm Bay	bay	root	1	2203	2019-09-01 12:39:38.652576	\N	\N	100
state:Hainaut:2792477	S2	Hainaut	state	region:Walloon:3337387	1	1531	2019-06-30 21:09:19.7267	\N	\N	100
state:SanJuan:3493091	S2	San Juan	state	country:DominicanRepublic:3508796	1	2037	2019-07-04 00:30:36.63079	\N	\N	100
region:Midlands:	S2	Midlands	region	country:Ireland:2963597	0	1854	2019-06-30 21:35:30.601116	\N	\N	100
state:Alajuela:3624953	S2	Alajuela	state	country:CostaRica:3624060	1	2039	2019-07-01 03:03:53.727138	\N	\N	100
state:Blantyre:931755	S2	Blantyre	state	region:Southern:923817	1	510	2019-07-03 16:24:27.994002	\N	\N	100
state:Canterbury:2192628	S2	Canterbury	state	region:SouthIsland:2182504	1	5893	2019-07-01 09:11:24.868705	\N	\N	100
state:Manufahi:1636309	S2	Manufahi	state	country:TimorLeste:1966436	1	2022	2019-06-30 10:33:26.681968	\N	\N	100
state:GrandKru:2588490	S2	Grand Kru	state	country:Liberia:2275384	1	2013	2019-06-30 22:28:08.828327	\N	\N	100
state:Nara:1855608	S2	Nara	state	region:Kinki:1859449	1	11	2019-11-17 04:40:28.304728	\N	\N	100
region:Southern:923817	S2	Southern	region	country:Malawi:927384	0	4261	2019-06-30 20:01:56.415801	\N	\N	100
region:Moravicki:7581800	S2	Moravički	region	country:Serbia:6290252	0	1022	2019-07-01 18:52:42.497183	\N	\N	100
state:JawaBarat:1642672	S2	Jawa Barat	state	country:Indonesia:1643084	1	5980	2019-07-01 13:02:11.58681	\N	\N	100
channel:RonneEntrance:6624356	S2	Ronne Entrance	channel	root	1	1731	2019-09-10 19:42:45.26743	\N	\N	100
state:MontePlata:3496132	S2	Monte Plata	state	country:DominicanRepublic:3508796	1	1551	2019-06-30 23:42:24.683571	\N	\N	100
state:Est:2231835	S2	Est	state	country:Cameroon:2233387	1	11299	2019-06-30 18:21:16.895427	\N	\N	100
sound:PeacockSound:6623007	S2	Peacock Sound	sound	root	1	1463	2019-09-07 21:05:09.376667	\N	\N	100
state:WestBerkshire:3333217	S2	West Berkshire	state	region:SouthEast:2637438	1	3	2019-09-02 15:18:37.648487	\N	\N	100
state:GornjiGrad:3239133	S2	Gornji Grad	state	region:Savinjska:3186550	1	2	2019-11-17 16:02:47.059609	\N	\N	100
state:Kekavas:7628322	S2	Kekavas	state	region:Riga:456173	1	2	2019-09-17 14:38:48.857474	\N	\N	100
nicestuff	*	nicestuff	hashtag	root	1	0	2019-11-25 09:17:12.619025	\N	\N	100
earthquake	*	earthquake	hashtag	root	1	0	2019-11-25 09:17:12.616684	\N	\N	100
state:Manafwa:11433403	S2	Manafwa	state	region:Eastern:8260673	1	3	2019-09-27 18:22:49.466732	\N	\N	100
state:Baldones:7628324	S2	Baldones	state	region:Riga:456173	1	1	2019-09-17 14:37:41.729418	\N	\N	100
state:Jaunjelgavas:7628333	S2	Jaunjelgavas	state	region:Zemgale:7639660	1	1	2019-09-17 15:06:55.016404	\N	\N	100
state:Livanu:7628341	S2	Livanu	state	region:Latgale:7639662	1	1	2019-09-17 14:44:06.554573	\N	\N	100
state:Napoli:3172391	S2	Napoli	state	region:Campania:3181042	1	8	2019-09-20 17:46:25.446518	\N	\N	100
gulf:GolfoDeTehuantepec:3516106	S2	Golfo de Tehuantepec	gulf	root	1	2571	2019-07-01 07:58:48.998143	\N	\N	100
state:Terni:3165770	S2	Terni	state	region:Umbria:3165048	1	13	2019-10-06 18:53:37.912566	\N	\N	100
state:StoengTreng:	S2	Stœng Trêng	state	country:Cambodia:1831722	1	1039	2019-06-30 12:38:52.529407	\N	\N	100
state:KwunTong:7533614	S2	Kwun Tong	state	region:Kowloon:1819609	1	274	2019-09-14 08:36:47.495314	\N	\N	100
state:AltoParaguay:3439441	S2	Alto Paraguay	state	country:Paraguay:3437598	1	14228	2019-06-30 10:10:29.194042	\N	\N	100
state:Chiayi:1678835	S2	Chiayi	state	region:TaiwanProvince:1668284	1	25	2019-09-28 06:35:41.67163	\N	\N	100
country:Haiti:3723988	S2	Haiti	country	continent:NorthAmerica:6255149	0	3971	2019-07-02 08:25:08.513693	\N	\N	100
state:Oran:2485920	S2	Oran	state	country:Algeria:2589581	1	2048	2019-06-30 22:53:18.980648	\N	\N	100
state:CiudadReal:2519401	S2	Ciudad Real	state	region:CastillaLaMancha:2593111	1	3562	2019-07-01 19:34:13.947789	\N	\N	100
state:Bergamo:3182163	S2	Bergamo	state	region:Lombardia:3174618	1	7	2019-09-19 17:54:37.885929	\N	\N	100
state:Gaza:1046058	S2	Gaza	state	country:Mozambique:1036973	1	7359	2019-06-30 20:03:14.738534	\N	\N	100
state:Cotopaxi:3658766	S2	Cotopaxi	state	country:Ecuador:3658394	1	651	2019-07-02 08:24:53.28936	\N	\N	100
bay:SulzbergerBay:6626260	S2	Sulzberger Bay	bay	root	1	2392	2019-09-18 23:55:09.022625	\N	\N	100
state:Kouffo:2597273	S2	Kouffo	state	country:Benin:2395170	1	996	2019-07-03 19:39:13.216853	\N	\N	100
state:Antioquia:3689815	S2	Antioquia	state	country:Colombia:3686110	1	6944	2019-07-02 08:21:30.674225	\N	\N	100
state:Capiz:1718641	S2	Capiz	state	region:WesternVisayas(RegionVi):	1	45	2019-11-06 04:52:52.164774	\N	\N	100
state:Nord:3719543	S2	Nord	state	country:Haiti:3723988	1	1499	2019-07-02 08:25:08.512157	\N	\N	100
state:AlaotraMangoro:7670851	S2	Alaotra-Mangoro	state	country:Madagascar:1062947	1	4278	2019-06-30 15:31:24.979232	\N	\N	100
state:ViboValentia:6457405	S2	Vibo Valentia	state	region:Calabria:2525468	1	140	2019-09-15 16:55:37.356552	\N	\N	100
region:Walloon:3337387	S2	Walloon	region	country:Belgium:2802361	0	4598	2019-06-30 21:06:37.852922	\N	\N	100
state:Gloucestershire:2648402	S2	Gloucestershire	state	region:SouthWest:11591956	1	608	2019-07-01 19:51:01.653029	\N	\N	100
state:Guelma:2495659	S2	Guelma	state	country:Algeria:2589581	1	1013	2019-07-02 01:39:24.664065	\N	\N	100
strait:DmitriyLaptevStrait	S2	Dmitriy Laptev Strait	strait	root	1	4861	2019-06-30 11:32:35.126262	\N	\N	100
state:Bolivar:3660130	S2	Bolivar	state	country:Ecuador:3658394	1	1176	2019-07-02 08:21:32.415434	\N	\N	100
state:JawaTengah:1642669	S2	Jawa Tengah	state	country:Indonesia:1643084	1	5107	2019-07-01 13:10:27.017128	\N	\N	100
state:Auces:7628312	S2	Auces	state	region:Zemgale:7639660	1	1	2019-09-27 16:23:24.787202	\N	\N	100
state:Aknistes:7628338	S2	Aknistes	state	region:Zemgale:7639660	1	1	2019-09-27 16:26:51.321628	\N	\N	100
state:BangkokMetropolis:1609348	S2	Bangkok Metropolis	state	region:Central:10177180	1	1	2019-11-28 09:47:02.318209	\N	\N	100
state:MisamisOriental:1699492	S2	Misamis Oriental	state	region:NorthernMindanao(RegionX):	1	13	2019-09-14 08:06:20.724037	\N	\N	100
state:Kourweogo:2597262	S2	Kourwéogo	state	region:PlateauCentral:6930712	1	7	2019-09-17 17:03:32.374396	\N	\N	100
state:MoravskeToplice:3239279	S2	Moravske Toplice	state	region:Pomurska:	1	3	2019-11-22 14:54:56.004383	\N	\N	100
state:Krustpils:7628355	S2	Krustpils	state	region:Zemgale:7639660	1	2	2019-09-17 14:44:06.555551	\N	\N	100
state:WestLothian:2634354	S2	West Lothian	state	region:Eastern:2650458	1	14	2019-10-08 16:20:51.00757	\N	\N	100
state:Dunaujvaros:3053438	S2	Dunaújváros	state	region:CentralTransdanubia:	1	1	2019-10-10 16:11:00.841689	\N	\N	100
state:Palencia:3114530	S2	Palencia	state	region:CastillaYLeon:3336900	1	7	2019-09-24 16:58:40.000592	\N	\N	100
state:Butaleja:8657920	S2	Butaleja	state	region:Eastern:8260673	1	4	2019-09-27 18:19:18.025857	\N	\N	100
state:Chimborazo:3659237	S2	Chimborazo	state	country:Ecuador:3658394	1	685	2019-07-02 08:21:32.416689	\N	\N	100
state:Bazega:2362644	S2	Bazéga	state	region:CentreSud:6930708	1	1026	2019-06-30 09:51:49.26621	\N	\N	100
state:Ilukstes:7628339	S2	Ilukstes	state	region:Latgale:7639662	1	511	2019-07-04 15:06:38.804279	\N	\N	100
state:Amazonas:3699699	S2	Amazonas	state	country:Peru:3932488	1	7184	2019-07-02 08:23:25.982642	\N	\N	100
state:Oruro:3909233	S2	Oruro	state	country:Bolivia:3923057	1	6383	2019-07-01 01:38:37.70778	\N	\N	100
country:Madagascar:1062947	S2	Madagascar	country	continent:Africa:6255146	0	59141	2019-06-30 15:15:13.696607	\N	\N	100
state:Barahona:3512042	S2	Barahona	state	country:DominicanRepublic:3508796	1	2028	2019-07-04 00:27:13.285448	\N	\N	100
state:NordOuest:3719536	S2	Nord-Ouest	state	country:Haiti:3723988	1	1478	2019-07-02 08:25:08.512731	\N	\N	100
state:Independencia:3504326	S2	Independencia	state	country:DominicanRepublic:3508796	1	1018	2019-07-04 00:30:36.631227	\N	\N	100
state:Tissemsilt:2475858	S2	Tissemsilt	state	country:Algeria:2589581	1	2035	2019-07-02 19:17:42.614298	\N	\N	100
state:Guayas:3657505	S2	Guayas	state	country:Ecuador:3658394	1	3553	2019-06-30 10:14:46.958372	\N	\N	100
channel:YucatanChannel:3514209	S2	Yucatan Channel	channel	root	1	1034	2019-07-02 01:29:25.13004	\N	\N	100
state:Napo:3650445	S2	Napo	state	country:Ecuador:3658394	1	987	2019-07-02 08:33:01.975176	\N	\N	100
region:CentreSud:6930708	S2	Centre-Sud	region	country:BurkinaFaso:2361809	0	2034	2019-06-30 09:51:49.266989	\N	\N	100
state:Tindouf:2476302	S2	Tindouf	state	country:Algeria:2589581	1	18458	2019-06-30 21:53:54.125048	\N	\N	100
state:Naryn:1527590	S2	Naryn	state	country:Kyrgyzstan:1527747	1	6217	2019-06-30 16:38:42.591898	\N	\N	100
state:KangwonDo:1876101	S2	Kangwŏn-do	state	country:NorthKorea:1873107	1	1551	2019-06-30 11:39:21.508987	\N	\N	100
strait:TaiwanStrait:1668281	S2	Taiwan Strait	strait	root	1	8453	2019-06-30 11:03:13.180519	\N	\N	100
state:Cordoba:3685889	S2	Córdoba	state	country:Colombia:3686110	1	2999	2019-07-02 08:21:30.673511	\N	\N	100
state:Marijampoles:864479	S2	Marijampoles	state	country:Lithuania:597427	1	1038	2019-07-02 17:53:24.340336	\N	\N	100
state:Nampula:1033354	S2	Nampula	state	country:Mozambique:1036973	1	8175	2019-06-30 20:17:24.658947	\N	\N	100
state:Corrientes:3435214	S2	Corrientes	state	country:Argentina:3865483	1	11759	2019-06-30 22:39:41.051766	\N	\N	100
region:CentralVisayas(RegionVii):	S2	Central Visayas (Region VII)	region	country:Philippines:1694008	0	2868	2019-06-30 14:08:21.223381	\N	\N	100
state:Csongrad:721589	S2	Csongrád	state	region:GreatSouthernPlain:	1	519	2019-07-04 16:14:37.441301	\N	\N	100
state:Muscat:286963	S2	Muscat	state	country:Oman:286963	1	1550	2019-06-30 15:45:55.115885	\N	\N	100
state:Uruzgan:1131461	S2	Uruzgan	state	country:Afghanistan:1149361	1	1514	2019-07-01 15:25:07.565237	\N	\N	100
state:Morogoro:153214	S2	Morogoro	state	country:Tanzania:149590	1	9744	2019-06-30 20:07:50.005692	\N	\N	100
sea:RossSea:4036625	S2	Ross Sea	sea	root	1	161819	2019-09-05 21:09:51.743256	\N	\N	100
state:Nantou:1671564	S2	Nantou	state	region:TaiwanProvince:1668284	1	996	2019-06-30 11:08:49.537452	\N	\N	100
state:Niassa:1030006	S2	Niassa	state	country:Mozambique:1036973	1	15230	2019-06-30 20:01:09.186859	\N	\N	100
state:Hamburg:2911297	S2	Hamburg	state	country:Germany:2921044	1	545	2019-07-02 19:04:41.870342	\N	\N	100
state:Telsiai:864483	S2	Telšiai	state	country:Lithuania:597427	1	2049	2019-07-02 17:54:58.685433	\N	\N	100
state:Medea:2488831	S2	Médéa	state	country:Algeria:2589581	1	1699	2019-06-30 09:51:53.433996	\N	\N	100
state:Lorestan:125605	S2	Lorestan	state	country:Iran:130758	1	5043	2019-06-30 19:21:43.353246	\N	\N	100
state:Illinois:4896861	S2	Illinois	state	region:Midwest:11887750	1	18440	2019-07-01 02:47:14.031321	\N	\N	100
state:Esmeraldas:3657986	S2	Esmeraldas	state	country:Ecuador:3658394	1	2474	2019-06-30 10:15:50.098644	\N	\N	100
state:SeineMaritime:2975248	S2	Seine-Maritime	state	region:HauteNormandie:3013756	1	2061	2019-06-30 21:17:43.235024	\N	\N	100
state:Mtwara:877744	S2	Mtwara	state	country:Tanzania:149590	1	3851	2019-06-30 20:10:55.560021	\N	\N	100
state:Vermont:5242283	S2	Vermont	state	region:Northeast:11887749	1	3600	2019-07-01 02:07:35.101305	\N	\N	100
state:Galapagos:3657879	S2	Galápagos	state	country:Ecuador:3658394	1	693	2019-07-02 03:08:56.074521	\N	\N	100
state:IdaViru:592075	S2	Ida-Viru	state	country:Estonia:453733	1	1692	2019-06-30 10:13:58.653371	\N	\N	100
state:LaEstrelleta:3507269	S2	La Estrelleta	state	country:DominicanRepublic:3508796	1	2039	2019-07-04 00:30:36.628876	\N	\N	100
state:VatovavyFitovinany:7670906	S2	Vatovavy-Fitovinany	state	country:Madagascar:1062947	1	3180	2019-06-30 15:37:27.238643	\N	\N	100
inlet:BairdInlet:5891749	S2	Baird Inlet	inlet	root	1	1316	2019-06-30 10:32:06.550934	\N	\N	100
state:Jharkhand:1444365	S2	Jharkhand	state	region:East:1272123	1	8131	2019-06-30 15:20:46.302204	\N	\N	100
state:SidiBouZid:2465837	S2	Sidi Bou Zid	state	country:Tunisia:2464461	1	1059	2019-06-30 18:08:10.409778	\N	\N	100
region:CentreOuest:6930707	S2	Centre-Ouest	region	country:BurkinaFaso:2361809	0	2097	2019-06-30 09:51:48.521835	\N	\N	100
state:PYongyang:1871859	S2	P'yŏngyang	state	country:NorthKorea:1873107	1	2047	2019-06-30 11:37:06.357998	\N	\N	100
state:AlMahwit:73200	S2	Al Mahwit	state	country:Yemen:69543	1	1046	2019-06-30 19:20:45.056704	\N	\N	100
gulf:GolfoDeUraba:3666448	S2	Golfo de Urabá	gulf	root	1	979	2019-07-02 08:25:46.45401	\N	\N	100
state:Harju:592170	S2	Harju	state	country:Estonia:453733	1	3218	2019-07-02 18:09:47.388395	\N	\N	100
state:Bechar:2505525	S2	Béchar	state	country:Algeria:2589581	1	18650	2019-06-30 21:51:10.941906	\N	\N	100
state:Brasov:683843	S2	Brasov	state	country:Romania:798549	1	1049	2019-06-30 18:09:25.099743	\N	\N	100
state:Bahoruco:3512050	S2	Bahoruco	state	country:DominicanRepublic:3508796	1	1015	2019-07-04 00:30:36.630363	\N	\N	100
region:HauteNormandie:3013756	S2	Haute-Normandie	region	country:France:3017382	0	4098	2019-06-30 21:11:05.550855	\N	\N	100
state:Kaliningrad:554230	S2	Kaliningrad	state	region:Northwestern:11961321	1	2578	2019-06-30 18:18:58.727318	\N	\N	100
state:Pernambuco:3392268	S2	Pernambuco	state	country:Brazil:3469034	1	10025	2019-06-30 21:53:00.113176	\N	\N	100
state:Hajjah:6201195	S2	Hajjah	state	country:Yemen:69543	1	1576	2019-06-30 19:20:45.057722	\N	\N	100
state:Setif:2481696	S2	Sétif	state	country:Algeria:2589581	1	1403	2019-07-02 01:38:45.553098	\N	\N	100
state:Laghman:1135022	S2	Laghman	state	country:Afghanistan:1149361	1	1964	2019-07-01 15:23:24.075519	\N	\N	100
state:Cajamarca:3699087	S2	Cajamarca	state	country:Peru:3932488	1	4596	2019-07-02 08:27:12.539864	\N	\N	100
state:HeardIslandAndMcdonaldIslands:1547314	S2	Heard Island and McDonald Islands	state	country:HeardIslandAndMcdonaldIslands:1547314	1	2919	2019-06-30 13:42:23.178193	\N	\N	100
state:Atsinanana:7670857	S2	Atsinanana	state	country:Madagascar:1062947	1	4307	2019-06-30 15:31:24.979919	\N	\N	100
state:Gafsa:2468351	S2	Gafsa	state	country:Tunisia:2464461	1	2547	2019-06-30 17:38:42.005412	\N	\N	100
state:Midtjylland:6418539	S2	Midtjylland	state	country:Denmark:2623032	1	2600	2019-06-30 21:27:37.375808	\N	\N	100
state:Choco:3686431	S2	Chocó	state	country:Colombia:3686110	1	4831	2019-07-02 08:23:29.920731	\N	\N	100
state:Taurages:864482	S2	Taurages	state	country:Lithuania:597427	1	2044	2019-07-02 17:53:10.452767	\N	\N	100
country:Ecuador:3658394	S2	Ecuador	country	continent:SouthAmerica:6255150	0	22170	2019-06-30 10:14:46.959644	\N	\N	100
state:Central:921064	S2	Central	state	country:Zambia:895949	1	10662	2019-07-01 17:07:55.11447	\N	\N	100
state:Putumayo:3671178	S2	Putumayo	state	country:Colombia:3686110	1	4198	2019-07-01 01:18:40.000233	\N	\N	100
state:LaPaz:3911924	S2	La Paz	state	country:Bolivia:3923057	1	15336	2019-07-02 00:43:36.46019	\N	\N	100
state:AlJawf:74222	S2	Al Jawf	state	country:Yemen:69543	1	5458	2019-06-30 19:19:48.599574	\N	\N	100
state:Sucumbios:3830305	S2	Sucumbios	state	country:Ecuador:3658394	1	1826	2019-07-02 08:22:41.244604	\N	\N	100
state:Hormozgan:131222	S2	Hormozgan	state	country:Iran:130758	1	9842	2019-06-30 15:45:05.909901	\N	\N	100
state:Bale:2597248	S2	Balé	state	region:BoucleDuMouhoun:6930701	1	1054	2019-06-30 09:51:48.520386	\N	\N	100
state:Tarapaca:3870116	S2	Tarapacá	state	country:Chile:3895114	1	5852	2019-07-02 00:41:50.964003	\N	\N	100
month:02	S2	02	month	root	1	1811014	2019-09-27 16:08:21.464555	\N	\N	100
state:AlHudaydah:79416	S2	Al Hudaydah	state	country:Yemen:69543	1	2126	2019-06-30 19:26:20.134978	\N	\N	100
state:SaDah:71333	S2	Sa`dah	state	country:Yemen:69543	1	2079	2019-06-30 19:27:57.193614	\N	\N	100
state:LaLibertad:3695781	S2	La Libertad	state	country:Peru:3932488	1	3958	2019-07-02 08:27:12.54033	\N	\N	100
state:AlBatnahNorth:	S2	Al Batnah North	state	country:Oman:286963	1	2349	2019-06-30 15:48:58.678545	\N	\N	100
state:NovaScotia:6091530	S2	Nova Scotia	state	region:EasternCanada:	1	10291	2019-06-30 23:39:40.892086	\N	\N	100
strait:KarskiyeStrait	S2	Karskiye Strait	strait	root	1	1423	2019-06-30 16:58:17.279163	\N	\N	100
state:Qazvin:443793	S2	Qazvin	state	country:Iran:130758	1	4045	2019-06-30 20:02:48.212822	\N	\N	100
state:Hyogo:1862047	S2	Hyōgo	state	region:Kinki:1859449	1	1986	2019-06-30 10:33:37.860346	\N	\N	100
region:MidiPyrenees:11071623	S2	Midi-Pyrénées	region	country:France:3017382	0	7471	2019-06-30 20:46:51.811317	\N	\N	100
gulf:GulfOfKutch:1268730	S2	Gulf of Kutch	gulf	root	1	2981	2019-06-30 18:04:12.375219	\N	\N	100
bay:BayOfFundy:5895392	S2	Bay of Fundy	bay	root	1	3692	2019-07-02 08:32:17.980216	\N	\N	100
state:Yatenga:2353568	S2	Yatenga	state	region:Nord:6930706	1	1026	2019-06-30 09:51:49.626984	\N	\N	100
state:Lahij:6201197	S2	Lahij	state	country:Yemen:69543	1	2093	2019-06-30 20:34:11.003562	\N	\N	100
state:Sarawak:1733038	S2	Sarawak	state	country:Malaysia:1733045	1	13701	2019-06-30 12:13:00.875879	\N	\N	100
state:Jarva:591961	S2	Järva	state	country:Estonia:453733	1	1597	2019-06-30 10:13:58.652894	\N	\N	100
state:MoronaSantiago:3654005	S2	Morona Santiago	state	country:Ecuador:3658394	1	3773	2019-07-02 08:21:32.416285	\N	\N	100
country:Tunisia:2464461	S2	Tunisia	country	continent:Africa:6255146	0	20053	2019-06-30 17:38:42.005867	\N	\N	100
state:Ioba:2597251	S2	Ioba	state	region:SudOuest:6930714	1	526	2019-06-30 09:51:48.519627	\N	\N	100
state:Cumbria:2651712	S2	Cumbria	state	region:NorthWest:2641227	1	2031	2019-07-02 20:15:11.474869	\N	\N	100
state:LibertadorGeneralBernardoOHiggins:3883281	S2	Libertador General Bernardo O'Higgins	state	country:Chile:3895114	1	3568	2019-07-01 01:39:41.603138	\N	\N	100
state:Parnu:589576	S2	Pärnu	state	country:Estonia:453733	1	3064	2019-07-02 18:10:17.0915	\N	\N	100
state:Canar:3659849	S2	Cañar	state	country:Ecuador:3658394	1	668	2019-07-02 08:21:32.415886	\N	\N	100
state:Salzburg:2766823	S2	Salzburg	state	country:Austria:2782113	1	2037	2019-06-30 18:01:32.312598	\N	\N	100
state:Atacama:3899191	S2	Atacama	state	country:Chile:3895114	1	9785	2019-07-02 00:45:06.16481	\N	\N	100
state:DhiQar:97019	S2	Dhi-Qar	state	region:Iraq:99237	1	1988	2019-06-30 19:20:29.231052	\N	\N	100
state:Laane:590856	S2	Lääne	state	country:Estonia:453733	1	2985	2019-06-30 18:03:43.577025	\N	\N	100
state:Rapla:589115	S2	Rapla	state	country:Estonia:453733	1	2029	2019-07-02 18:10:17.092452	\N	\N	100
region:Lombardia:3174618	S2	Lombardia	region	country:Italy:3175395	0	4091	2019-07-02 01:39:48.171951	\N	\N	100
state:Arta:449265	S2	Arta	state	country:Djibouti:223816	1	1030	2019-06-30 20:44:14.373866	\N	\N	100
state:Dikhil:223889	S2	Dikhil	state	country:Djibouti:223816	1	1032	2019-06-30 20:44:14.374818	\N	\N	100
state:Sissili:2355474	S2	Sissili	state	region:CentreOuest:6930707	1	523	2019-06-30 09:51:48.521103	\N	\N	100
state:Hamadan:132142	S2	Hamadan	state	country:Iran:130758	1	3504	2019-06-30 19:54:55.559905	\N	\N	100
state:Gilan:133349	S2	Gilan	state	country:Iran:130758	1	2516	2019-06-30 20:02:48.211892	\N	\N	100
state:TaIzz:70222	S2	Ta`izz	state	country:Yemen:69543	1	1591	2019-06-30 19:25:09.059223	\N	\N	100
state:PyreneesAtlantiques:2984887	S2	Pyrénées-Atlantiques	state	region:Aquitaine:11071620	1	2149	2019-06-30 21:11:05.986876	\N	\N	100
state:Tungurahua:3653890	S2	Tungurahua	state	country:Ecuador:3658394	1	1807	2019-07-02 08:32:15.106274	\N	\N	100
region:Aquitaine:11071620	S2	Aquitaine	region	country:France:3017382	0	9413	2019-06-30 20:46:51.810079	\N	\N	100
state:Brescia:3181553	S2	Brescia	state	region:Lombardia:3174618	1	2022	2019-07-02 01:39:48.171191	\N	\N	100
state:SouthCaicosAndEastCaicos:	S2	South Caicos and East Caicos	state	country:TurksAndCaicosIslands:3576916	1	2012	2019-07-03 21:49:41.852483	\N	\N	100
state:Leicestershire:2644667	S2	Leicestershire	state	region:EastMidlands:11591952	1	507	2019-07-04 17:36:11.261821	\N	\N	100
state:HautesPyrenees:3013726	S2	Hautes-Pyrénées	state	region:MidiPyrenees:11071623	1	1059	2019-06-30 21:35:26.005857	\N	\N	100
state:Kerala:1267254	S2	Kerala	state	region:South:8335041	1	3202	2019-07-01 17:19:47.804111	\N	\N	100
state:Vlore:3344952	S2	Vlorë	state	country:Albania:783754	1	1013	2019-07-01 18:54:05.733307	\N	\N	100
state:Tuva:1488873	S2	Tuva	state	region:Siberian:11961345	1	32997	2019-06-30 12:59:13.468048	\N	\N	100
state:Dundgovi:2031740	S2	Dundgovi	state	country:Mongolia:2029969	1	10608	2019-06-30 13:49:17.57468	\N	\N	100
state:PuertoPrincesa:1692682	S2	Puerto Princesa	state	region:Mimaropa(RegionIvB):	1	1016	2019-06-30 12:12:37.314152	\N	\N	100
region:SudOuest:6930714	S2	Sud-Ouest	region	country:BurkinaFaso:2361809	0	1080	2019-06-30 09:51:47.464104	\N	\N	100
state:Hovd:1515696	S2	Hovd	state	country:Mongolia:2029969	1	12240	2019-06-30 15:33:06.29549	\N	\N	100
state:Hawaii:5855797	S2	Hawaii	state	region:West:11887751	1	4926	2019-07-01 08:19:08.141936	\N	\N	100
landcover:flooded	S2	Flooded	landcover:flooded	root	1	54682	2019-06-30 10:14:07.924812	\N	\N	100
region:ForalDeNavarra:3115609	S2	Foral de Navarra	region	country:Spain:2510769	0	1035	2019-07-03 20:05:05.483895	\N	\N	100
state:Cuenca:2519034	S2	Cuenca	state	region:CastillaLaMancha:2593111	1	2067	2019-06-30 20:54:54.147442	\N	\N	100
state:Tuy:2597267	S2	Tuy	state	region:HautsBassins:6930710	1	1041	2019-07-02 20:52:58.93925	\N	\N	100
state:YsykKol:1528260	S2	Ysyk-Köl	state	country:Kyrgyzstan:1527747	1	9203	2019-06-30 16:40:32.667083	\N	\N	100
state:PrinceEdwardIsland:6113358	S2	Prince Edward Island	state	region:EasternCanada:	1	2438	2019-06-30 23:43:27.499515	\N	\N	100
state:TavastiaProper:830705	S2	Tavastia Proper	state	country:Finland:660013	1	3787	2019-06-30 17:57:25.561689	\N	\N	100
state:Vichada:3666082	S2	Vichada	state	country:Colombia:3686110	1	10041	2019-06-30 10:14:02.894537	\N	\N	100
state:Karas:3371201	S2	Karas	state	country:Namibia:3355338	1	17567	2019-06-30 20:09:45.495383	\N	\N	100
state:Ibb:6201196	S2	Ibb	state	country:Yemen:69543	1	1570	2019-06-30 19:19:45.92957	\N	\N	100
state:Salto:3440711	S2	Salto	state	country:Uruguay:3439705	1	1875	2019-06-30 22:40:38.750128	\N	\N	100
state:Sanmatenga:2355914	S2	Sanmatenga	state	region:CentreNord:6930706	1	2769	2019-06-30 09:51:49.630221	\N	\N	100
bay:UngavaBay:6171924	S2	Ungava Bay	bay	root	1	12630	2019-07-01 02:18:50.044517	\N	\N	100
state:RioDeJaneiro:3451189	S2	Rio de Janeiro	state	country:Brazil:3469034	1	8472	2019-06-30 21:50:04.045375	\N	\N	100
country:AntiguaAndBarbuda:3576396	S2	Antigua and Barbuda	country	continent:NorthAmerica:6255149	0	1029	2019-07-02 00:13:30.789563	\N	\N	100
state:Colon:3613358	S2	Colón	state	country:Honduras:3608932	1	1748	2019-07-02 02:15:19.903964	\N	\N	100
state:Khenchela:2491887	S2	Khenchela	state	country:Algeria:2589581	1	4063	2019-07-02 01:42:36.353855	\N	\N	100
state:Maputo:1040649	S2	Maputo	state	country:Mozambique:1036973	1	3225	2019-06-30 21:40:23.364837	\N	\N	100
region:Veneto:3164604	S2	Veneto	region	country:Italy:3175395	0	2583	2019-06-30 18:20:29.488772	\N	\N	100
region:Norte:2373280	S2	Norte	region	country:GuineaBissau:2372248	0	1038	2019-07-04 17:50:52.433736	\N	\N	100
state:Paysandu:3441242	S2	Paysandú	state	country:Uruguay:3439705	1	1573	2019-06-30 22:38:38.325291	\N	\N	100
country:TurksAndCaicosIslands:3576916	S2	Turks and Caicos Islands	country	continent:NorthAmerica:6255149	0	1986	2019-07-02 08:38:39.020344	\N	\N	100
state:Illizi:2493455	S2	Illizi	state	country:Algeria:2589581	1	23305	2019-06-30 17:37:37.130549	\N	\N	100
state:Logar:1134561	S2	Logar	state	country:Afghanistan:1149361	1	1251	2019-07-01 15:17:39.507741	\N	\N	100
region:HautsBassins:6930710	S2	Hauts-Bassins	region	country:BurkinaFaso:2361809	0	3632	2019-06-30 09:51:46.686009	\N	\N	100
state:Fier:3344948	S2	Fier	state	country:Albania:783754	1	1007	2019-07-01 18:54:05.732838	\N	\N	100
state:TristanDaCunha:3370684	S2	Tristan da Cunha	state	country:SaintHelena:3370751	1	1962	2019-07-02 00:18:20.639959	\N	\N	100
region:Aragon:3336899	S2	Aragón	region	country:Spain:2510769	0	9211	2019-06-30 21:06:22.895539	\N	\N	100
state:Balkh:1147288	S2	Balkh	state	country:Afghanistan:1149361	1	2753	2019-07-01 15:25:57.77191	\N	\N	100
state:Ternopil:691649	S2	Ternopil'	state	country:Ukraine:690791	1	3583	2019-07-01 18:43:15.103477	\N	\N	100
region:NaxcivanAutonomousRepublic:	S2	Naxçıvan Autonomous Republic	region	country:Azerbaijan:587116	0	993	2019-07-01 16:37:42.01945	\N	\N	100
state:Annaba:2506994	S2	Annaba	state	country:Algeria:2589581	1	1998	2019-07-02 01:39:24.663106	\N	\N	100
region:Mayotte:	S2	Mayotte	region	country:France:3017382	0	1054	2019-07-01 18:28:57.719316	\N	\N	100
state:Pordenone:3170146	S2	Pordenone	state	region:FriuliVeneziaGiulia:3176525	1	2024	2019-06-30 18:20:29.018106	\N	\N	100
state:KwazuluNatal:972062	S2	KwaZulu-Natal	state	country:SouthAfrica:953987	1	13607	2019-06-30 21:34:49.480697	\N	\N	100
region:HighlandsAndIslands:6621347	S2	Highlands and Islands	region	country:UnitedKingdom:2635167	0	10448	2019-06-30 18:32:33.466308	\N	\N	100
region:Nord:6930706	S2	Nord	region	country:BurkinaFaso:2361809	0	1605	2019-06-30 09:51:48.687375	\N	\N	100
country:Djibouti:223816	S2	Djibouti	country	continent:Africa:6255146	0	2074	2019-06-30 20:44:14.375271	\N	\N	100
state:Treviso:3165200	S2	Treviso	state	region:Veneto:3164604	1	499	2019-07-03 18:04:44.837579	\N	\N	100
state:Navarra:3115609	S2	Navarra	state	region:ForalDeNavarra:3115609	1	1036	2019-07-03 20:05:05.483143	\N	\N	100
state:Bougouriba:2362047	S2	Bougouriba	state	region:SudOuest:6930714	1	519	2019-07-04 19:25:58.228666	\N	\N	100
state:Puno:3931275	S2	Puno	state	country:Peru:3932488	1	8832	2019-06-30 10:15:06.27369	\N	\N	100
region:CordilleraAdministrativeRegion(Car):	S2	Cordillera Administrative Region (CAR)	region	country:Philippines:1694008	0	4138	2019-06-30 11:08:07.46964	\N	\N	100
state:VestAgder:3132064	S2	Vest-Agder	state	country:Norway:3144096	1	3209	2019-06-30 21:12:07.652741	\N	\N	100
country:Armenia:174982	S2	Armenia	country	continent:Asia:6255147	0	4969	2019-07-01 16:10:46.968103	\N	\N	100
state:Taraba:2597366	S2	Taraba	state	country:Nigeria:2328926	1	6310	2019-07-01 18:51:39.487246	\N	\N	100
state:Mogilev:625073	S2	Mogilev	state	country:Belarus:630336	1	8268	2019-06-30 17:30:59.592812	\N	\N	100
state:MontBuxton:241252	S2	Mont Buxton	state	country:Seychelles:241170	1	1118	2019-07-02 14:48:34.90545	\N	\N	100
state:NorthLebanon:	S2	North Lebanon	state	country:Lebanon:272103	1	2046	2019-06-30 19:11:35.832379	\N	\N	100
state:Glacis:241336	S2	Glacis	state	country:Seychelles:241170	1	1891	2019-07-02 14:48:34.908109	\N	\N	100
state:AnseEtoile:241447	S2	Anse Etoile	state	country:Seychelles:241170	1	1891	2019-07-02 14:48:34.908959	\N	\N	100
state:Albacete:2522257	S2	Albacete	state	region:CastillaLaMancha:2593111	1	4083	2019-06-30 22:42:23.562371	\N	\N	100
country:Peru:3932488	S2	Peru	country	continent:SouthAmerica:6255150	0	122045	2019-06-30 10:14:37.158216	\N	\N	100
state:KvemoKartli:865536	S2	Kvemo Kartli	state	country:Georgia:614540	1	2493	2019-07-01 16:10:46.965182	\N	\N	100
state:Buskerud:3159665	S2	Buskerud	state	country:Norway:3144096	1	4832	2019-06-30 21:07:54.608565	\N	\N	100
state:Oudalan:2356982	S2	Oudalan	state	region:Sahel:6930713	1	1540	2019-07-02 00:34:28.975433	\N	\N	100
state:Khost:1444362	S2	Khost	state	country:Afghanistan:1149361	1	1348	2019-07-01 15:29:04.543272	\N	\N	100
state:Gujarat:1270770	S2	Gujarat	state	region:West:1252881	1	23499	2019-06-30 18:02:39.793613	\N	\N	100
sea:SibuyanSea:1686700	S2	Sibuyan Sea	sea	root	1	4820	2019-06-30 14:08:07.284917	\N	\N	100
country:Lebanon:272103	S2	Lebanon	country	continent:Asia:6255147	0	2051	2019-06-30 19:11:35.833848	\N	\N	100
state:Shandong:1796328	S2	Shandong	state	region:EastChina:6493581	1	22496	2019-07-01 00:46:32.890173	\N	\N	100
state:Telemark:3134723	S2	Telemark	state	country:Norway:3144096	1	3928	2019-06-30 21:07:54.609218	\N	\N	100
state:BelAir:241426	S2	Bel Air	state	country:Seychelles:241170	1	964	2019-07-02 14:48:34.909407	\N	\N	100
state:Zambezia:1024312	S2	Zambezia	state	country:Mozambique:1036973	1	11654	2019-06-30 19:55:31.757398	\N	\N	100
state:Malatya:304919	S2	Malatya	state	country:Turkey:298795	1	3080	2019-06-30 19:15:08.659777	\N	\N	100
state:GrandAnse:241330	S2	Grand'Anse	state	country:Seychelles:241170	1	917	2019-07-02 14:48:34.908531	\N	\N	100
state:SaintLouis:241181	S2	Saint Louis	state	country:Seychelles:241170	1	964	2019-07-02 14:48:34.905951	\N	\N	100
state:Ruvuma:877416	S2	Ruvuma	state	country:Tanzania:149590	1	8539	2019-06-30 19:55:48.797613	\N	\N	100
state:MindoroOccidental:1699586	S2	Mindoro Occidental	state	region:Mimaropa(RegionIvB):	1	1557	2019-07-02 09:41:22.613774	\N	\N	100
state:PortGlaud:241215	S2	Port Glaud	state	country:Seychelles:241170	1	963	2019-07-02 14:48:34.910334	\N	\N	100
country:SaintBarthelemy:3578476	S2	Saint-Barthélemy	country	continent:NorthAmerica:6255149	0	931	2019-06-30 10:10:41.935794	\N	\N	100
country:Azerbaijan:587116	S2	Azerbaijan	country	continent:Asia:6255147	0	16513	2019-06-30 19:49:02.70063	\N	\N	100
state:MeknesTafilalet:2542710	S2	Meknès - Tafilalet	state	country:Morocco:2542007	1	11524	2019-06-30 22:24:15.405355	\N	\N	100
state:Bihar:1275715	S2	Bihar	state	region:East:1272123	1	12797	2019-06-30 15:29:58.998557	\N	\N	100
state:Culfa:148132	S2	Culfa	state	region:NaxcivanAutonomousRepublic:	1	492	2019-07-03 16:56:57.705279	\N	\N	100
state:SouthernNationsNationalitiesAndPeoples:444188	S2	Southern Nations, Nationalities and Peoples	state	country:Ethiopia:337996	1	12831	2019-07-01 16:10:49.475098	\N	\N	100
state:EnglishRiver:241302	S2	English River	state	country:Seychelles:241170	1	1891	2019-07-02 14:48:34.907246	\N	\N	100
state:Zhytomyr:686966	S2	Zhytomyr	state	country:Ukraine:690791	1	4103	2019-06-30 17:37:41.436107	\N	\N	100
region:Sahel:6930713	S2	Sahel	region	country:BurkinaFaso:2361809	0	5663	2019-07-02 00:34:28.976128	\N	\N	100
state:MontFleuri:241251	S2	Mont Fleuri	state	country:Seychelles:241170	1	1425	2019-07-02 14:48:34.906396	\N	\N	100
region:CastillaLaMancha:2593111	S2	Castilla-La Mancha	region	country:Spain:2510769	0	13789	2019-06-30 20:54:54.148088	\N	\N	100
state:Shirak:828264	S2	Shirak	state	country:Armenia:174982	1	1982	2019-07-01 16:10:46.967004	\N	\N	100
state:Lori:828263	S2	Lori	state	country:Armenia:174982	1	987	2019-07-01 16:10:46.967656	\N	\N	100
state:BeauVallon:241428	S2	Beau Vallon	state	country:Seychelles:241170	1	1891	2019-07-02 14:48:34.907683	\N	\N	100
country:Nicaragua:3617476	S2	Nicaragua	country	continent:NorthAmerica:6255149	0	16528	2019-07-01 03:05:53.79069	\N	\N	100
state:Plaisance:241224	S2	Plaisance	state	country:Seychelles:241170	1	909	2019-07-02 14:48:34.906808	\N	\N	100
state:Kalinga:7115730	S2	Kalinga	state	region:CordilleraAdministrativeRegion(Car):	1	510	2019-07-02 10:11:28.675982	\N	\N	100
sound:CumberlandSound:5933908	S2	Cumberland Sound	sound	root	1	8773	2019-06-30 10:15:15.310984	\N	\N	100
fjord:Boknafjorden:3160814	S2	Boknafjorden	fjord	root	1	2448	2019-06-30 21:27:41.775944	\N	\N	100
state:Abseron:587245	S2	Abşeron	state	region:AbsheronEconomicRegion:	1	994	2019-06-30 20:05:06.062586	\N	\N	100
state:Rogaland:3141558	S2	Rogaland	state	country:Norway:3144096	1	3748	2019-06-30 21:06:31.731562	\N	\N	100
state:Chanthaburi:1611268	S2	Chanthaburi	state	region:Eastern:7114724	1	1501	2019-07-01 15:13:04.692368	\N	\N	100
state:GreaterPoland:3337498	S2	Greater Poland	state	country:Poland:798544	1	7321	2019-06-30 18:00:59.632484	\N	\N	100
state:Beqaa:280282	S2	Beqaa	state	country:Lebanon:272103	1	1539	2019-06-30 19:11:35.833372	\N	\N	100
state:Steiermark:2764581	S2	Steiermark	state	country:Austria:2782113	1	1724	2019-06-30 18:01:58.492841	\N	\N	100
state:AshSharqiyahNorth:8394433	S2	Ash Sharqiyah North	state	country:Oman:286963	1	3701	2019-06-30 15:53:40.205637	\N	\N	100
state:Tunceli:298845	S2	Tunceli	state	country:Turkey:298795	1	1027	2019-07-02 16:30:11.963938	\N	\N	100
state:Kralovehradecky:3339540	S2	Královéhradecký	state	country:CzechRepublic:3077311	1	1773	2019-06-30 18:18:16.405935	\N	\N	100
state:Mardin:304794	S2	Mardin	state	country:Turkey:298795	1	2012	2019-07-02 17:26:56.742796	\N	\N	100
state:Tafilah:250092	S2	Tafilah	state	country:Jordan:248816	1	1535	2019-06-30 19:07:58.961814	\N	\N	100
state:WestPomeranian:3337499	S2	West Pomeranian	state	country:Poland:798544	1	5653	2019-06-30 18:00:59.632005	\N	\N	100
state:Powys:2639944	S2	Powys	state	region:WestWalesAndTheValleys:	1	2011	2019-07-02 19:42:35.496411	\N	\N	100
state:Masovian:858787	S2	Masovian	state	country:Poland:798544	1	5268	2019-06-30 18:06:21.206237	\N	\N	100
state:Kayanza:430951	S2	Kayanza	state	country:Burundi:433561	1	1102	2019-07-02 15:49:37.402351	\N	\N	100
region:DaghligShirvanEconomicRegion:	S2	Daghlig Shirvan Economic Region	region	country:Azerbaijan:587116	0	4991	2019-06-30 19:55:09.528112	\N	\N	100
region:ObalnoKraska:	S2	Obalno-kraška	region	country:Slovenia:3190538	0	1723	2019-06-30 18:02:41.806022	\N	\N	100
state:Kilimanjaro:157449	S2	Kilimanjaro	state	country:Tanzania:149590	1	1567	2019-06-30 20:47:55.433045	\N	\N	100
state:Hirat:1140025	S2	Hirat	state	country:Afghanistan:1149361	1	7769	2019-06-30 14:44:47.553136	\N	\N	100
state:Jawzjan:1139049	S2	Jawzjan	state	country:Afghanistan:1149361	1	4547	2019-07-02 14:01:12.196168	\N	\N	100
state:Gwynedd:2647716	S2	Gwynedd	state	region:EastWales:7302126	1	1002	2019-07-02 19:42:35.495011	\N	\N	100
state:LaaneViru:590854	S2	Lääne-Viru	state	country:Estonia:453733	1	1951	2019-06-30 10:13:58.654112	\N	\N	100
state:MountLebanon:9062341	S2	Mount Lebanon	state	country:Lebanon:272103	1	1019	2019-06-30 19:11:35.832898	\N	\N	100
state:DistritoFederal:3463504	S2	Distrito Federal	state	country:Brazil:3469034	1	2028	2019-06-30 09:52:42.509176	\N	\N	100
state:TawiTawi:1682671	S2	Tawi-Tawi	state	region:AutonomousRegionInMuslimMindanao(Armm):	1	506	2019-07-02 09:08:23.158868	\N	\N	100
region:WestWalesAndTheValleys:	S2	West Wales and the Valleys	region	country:UnitedKingdom:2635167	0	3010	2019-07-01 19:13:31.884534	\N	\N	100
region:EastWales:7302126	S2	East Wales	region	country:UnitedKingdom:2635167	0	3541	2019-07-02 19:28:24.378736	\N	\N	100
state:Karaman:443187	S2	Karaman	state	country:Turkey:298795	1	3024	2019-07-01 22:05:05.942701	\N	\N	100
state:Mordovia:525369	S2	Mordovia	state	region:Volga:11961325	1	4117	2019-06-30 19:02:34.63795	\N	\N	100
region:AranEconomicRegion:	S2	Aran Economic Region	region	country:Azerbaijan:587116	0	5993	2019-06-30 19:49:02.6993	\N	\N	100
state:Nizhegorod:559838	S2	Nizhegorod	state	region:Volga:11961325	1	12054	2019-06-30 19:01:49.395102	\N	\N	100
state:Ankara:323784	S2	Ankara	state	country:Turkey:298795	1	3431	2019-07-01 22:34:58.433789	\N	\N	100
state:Stredocesky:3339576	S2	Středočeský	state	country:CzechRepublic:3077311	1	1993	2019-06-30 18:16:29.611812	\N	\N	100
state:LuhansK:702658	S2	Luhans'k	state	country:Ukraine:690791	1	5118	2019-06-30 19:03:47.742313	\N	\N	100
state:Qobustan:828301	S2	Qobustan	state	region:DaghligShirvanEconomicRegion:	1	1986	2019-06-30 20:05:06.060783	\N	\N	100
state:Kinkkale:443188	S2	Kinkkale	state	country:Turkey:298795	1	1332	2019-07-01 22:37:00.195357	\N	\N	100
region:AbsheronEconomicRegion:	S2	Absheron Economic Region	region	country:Azerbaijan:587116	0	2008	2019-06-30 20:05:06.06303	\N	\N	100
state:Damascus:170652	S2	Damascus	state	country:Syria:163843	1	1019	2019-06-30 19:11:35.834329	\N	\N	100
state:Karak:250625	S2	Karak	state	country:Jordan:248816	1	2034	2019-06-30 19:14:13.425092	\N	\N	100
state:LaDigueAndInnerIslands:241311	S2	La Digue and Inner Islands	state	country:Seychelles:241170	1	1411	2019-07-02 14:56:28.1651	\N	\N	100
state:Hardap:3371200	S2	Hardap	state	country:Namibia:3355338	1	15404	2019-07-01 22:27:40.888179	\N	\N	100
state:Haryana:1270260	S2	Haryana	state	region:Central:8335421	1	3660	2019-07-02 13:42:16.342328	\N	\N	100
state:Kashkadarya:1114928	S2	Kashkadarya	state	country:Uzbekistan:1512440	1	5551	2019-07-02 14:02:42.31268	\N	\N	100
state:MindoroOriental:1699585	S2	Mindoro Oriental	state	region:Mimaropa(RegionIvB):	1	1025	2019-07-02 09:48:59.265444	\N	\N	100
state:Hajigabul:828315	S2	Hajigabul	state	region:AranEconomicRegion:	1	992	2019-06-30 20:05:06.061684	\N	\N	100
country:CzechRepublic:3077311	S2	Czech Republic	country	continent:Europe:6255148	0	14253	2019-06-30 18:03:10.560791	\N	\N	100
state:NusaTenggaraTimur:1633791	S2	Nusa Tenggara Timur	state	country:Indonesia:1643084	1	9201	2019-06-30 10:33:36.90855	\N	\N	100
state:Maysan:93540	S2	Maysan	state	region:Iraq:99237	1	1488	2019-06-30 19:22:05.176528	\N	\N	100
state:Shropshire:2638655	S2	Shropshire	state	region:WestMidlands:11591953	1	1011	2019-07-04 17:26:42.771127	\N	\N	100
state:Ipeiros:6697804	S2	Ipeiros	state	country:Greece:390903	1	2459	2019-07-01 19:00:42.312852	\N	\N	100
state:Jizan:105298	S2	Jizan	state	country:SaudiArabia:102358	1	3167	2019-06-30 19:22:46.665343	\N	\N	100
gulf:GulfOfKhambhat	S2	Gulf of Khambhät	gulf	root	1	4560	2019-06-30 18:09:47.0834	\N	\N	100
sea:FloresSea:1818200	S2	Flores Sea	sea	root	1	13847	2019-07-01 12:00:29.200565	\N	\N	100
state:Omaheke:3371205	S2	Omaheke	state	country:Namibia:3355338	1	12840	2019-06-30 10:10:21.997242	\N	\N	100
state:NorthEastern:400745	S2	North-Eastern	state	country:Kenya:192950	1	11389	2019-06-30 20:12:54.216159	\N	\N	100
state:Floresti:618331	S2	Floreşti	state	country:Moldova:617790	1	4130	2019-06-30 18:39:36.845359	\N	\N	100
state:Aceh:1215638	S2	Aceh	state	country:Indonesia:1643084	1	6784	2019-07-01 14:26:06.822272	\N	\N	100
state:YevlakhRayon:584650	S2	Yevlakh Rayon	state	region:AranEconomicRegion:	1	987	2019-07-01 16:14:16.794966	\N	\N	100
state:Thessalia:6697809	S2	Thessalia	state	country:Greece:390903	1	1954	2019-07-01 19:00:42.312293	\N	\N	100
state:Oriental:11281875	S2	Oriental	state	country:Morocco:2542007	1	8100	2019-06-30 22:25:03.040621	\N	\N	100
state:Ovorhangay:2029546	S2	Övörhangay	state	country:Mongolia:2029969	1	12470	2019-06-30 13:31:13.423175	\N	\N	100
state:Jegunovce:863858	S2	Jegunovce	state	country:Macedonia:718075	1	972	2019-07-01 18:37:40.408814	\N	\N	100
state:KowloonCity:7533612	S2	Kowloon City	state	region:Kowloon:1819609	1	1722	2019-07-01 11:51:00.364418	\N	\N	100
state:Kherson:706442	S2	Kherson	state	country:Ukraine:690791	1	4537	2019-07-01 22:28:24.715799	\N	\N	100
state:NusaTenggaraBarat:1633792	S2	Nusa Tenggara Barat	state	country:Indonesia:1643084	1	2136	2019-07-02 09:43:14.145721	\N	\N	100
state:Samux:828302	S2	Samux	state	region:GanjaGazakhEconomicRegion:	1	988	2019-07-01 16:14:16.795432	\N	\N	100
state:Pingtung:1670479	S2	Pingtung	state	region:TaiwanProvince:1668284	1	1502	2019-06-30 11:11:57.067304	\N	\N	100
state:KaohsiungCity:1673820	S2	Kaohsiung City	state	region:SpecialMunicipalities:	1	1001	2019-06-30 11:09:48.339081	\N	\N	100
region:GanjaGazakhEconomicRegion:	S2	Ganja-Gazakh Economic Region	region	country:Azerbaijan:587116	0	3460	2019-07-01 16:14:16.795857	\N	\N	100
state:Chuvash:567395	S2	Chuvash	state	region:Volga:11961325	1	2038	2019-06-30 19:03:50.749449	\N	\N	100
country:SiachenGlacier:1164949	S2	Siachen Glacier	country	continent:Asia:6255147	0	2042	2019-07-02 13:34:35.808762	\N	\N	100
region:BlueNile:408654	S2	Blue Nile	region	country:Sudan:366755	0	15851	2019-06-30 16:06:01.512157	\N	\N	100
region:ShakiZaqatalaEconomicRegion:	S2	Shaki-Zaqatala Economic Region	region	country:Azerbaijan:587116	0	2979	2019-07-01 16:14:16.796696	\N	\N	100
state:Salacgrivas:7628367	S2	Salacgrivas	state	region:Riga:456173	1	1028	2019-07-02 18:12:52.956924	\N	\N	100
state:Oslomej:863885	S2	Oslomej	state	country:Macedonia:718075	1	1478	2019-07-01 18:37:40.409307	\N	\N	100
state:LesserPoland:858786	S2	Lesser Poland	state	country:Poland:798544	1	3113	2019-07-02 17:48:39.111	\N	\N	100
state:Tearce:863906	S2	Tearce	state	country:Macedonia:718075	1	972	2019-07-01 18:37:40.410254	\N	\N	100
state:Transnistria:618363	S2	Transnistria	state	country:Moldova:617790	1	1032	2019-06-30 18:39:36.844033	\N	\N	100
state:Guadalajara:3121069	S2	Guadalajara	state	region:CastillaLaMancha:2593111	1	1537	2019-06-30 21:14:44.666061	\N	\N	100
state:TazaAlHoceimaTaounate:2597557	S2	Taza - Al Hoceima - Taounate	state	country:Morocco:2542007	1	3098	2019-06-30 22:53:11.138706	\N	\N	100
state:Chisinau:618069	S2	Chişinău	state	country:Moldova:617790	1	1029	2019-06-30 18:39:36.844695	\N	\N	100
state:Soria:3108680	S2	Soria	state	region:CastillaYLeon:3336900	1	1889	2019-07-01 19:31:58.652303	\N	\N	100
state:Sanliurfa:298332	S2	Sanliurfa	state	country:Turkey:298795	1	2721	2019-06-30 19:19:36.006955	\N	\N	100
state:Ialoveni:617991	S2	Ialoveni	state	country:Moldova:617790	1	2065	2019-06-30 18:39:36.848571	\N	\N	100
region:Kowloon:1819609	S2	Kowloon	region	country:HongKong:1819729	0	1998	2019-07-01 11:51:00.364915	\N	\N	100
state:SaiKung:1819049	S2	Sai Kung	state	region:TheNewTerritories:	1	993	2019-07-01 11:51:00.366342	\N	\N	100
state:Southern:7533609	S2	Southern	state	region:HongKongIsland:11791597	1	1996	2019-07-01 11:51:00.365361	\N	\N	100
region:HongKongIsland:11791597	S2	Hong Kong Island	region	country:HongKong:1819729	0	1996	2019-07-01 11:51:00.365822	\N	\N	100
country:Moldova:617790	S2	Moldova	country	continent:Europe:6255148	0	5786	2019-06-30 18:39:36.853779	\N	\N	100
region:TheNewTerritories:	S2	The New Territories	region	country:HongKong:1819729	0	2511	2019-07-01 11:51:00.366844	\N	\N	100
country:HongKong:1819729	S2	Hong Kong	country	continent:Asia:6255147	0	2509	2019-07-01 11:51:00.367292	\N	\N	100
state:Western:2101556	S2	Western	state	country:SolomonIslands:2103350	1	1486	2019-06-30 18:43:17.837752	\N	\N	100
state:JanubSina:355182	S2	Janub Sina'	state	country:Egypt:357994	1	4199	2019-06-30 19:04:11.992455	\N	\N	100
state:Adana:325361	S2	Adana	state	country:Turkey:298795	1	5873	2019-06-30 19:06:18.453545	\N	\N	100
state:Cimislia:618430	S2	Cimişlia	state	country:Moldova:617790	1	1031	2019-06-30 19:04:33.086624	\N	\N	100
state:Rovigo:3168842	S2	Rovigo	state	region:Veneto:3164604	1	1532	2019-06-30 18:17:40.999631	\N	\N	100
state:KarachayCherkess:	S2	Karachay-Cherkess	state	region:Volga:11961325	1	1514	2019-07-02 17:23:08.381622	\N	\N	100
state:Berlin:6547539	S2	Berlin	state	country:Germany:2921044	1	3013	2019-07-02 01:26:14.525786	\N	\N	100
state:Khomas:3352137	S2	Khomas	state	country:Namibia:3355338	1	5275	2019-07-01 22:58:28.760584	\N	\N	100
gulf:GulfOfYana	S2	Gulf of Yana	gulf	root	1	26438	2019-06-30 11:32:23.582304	\N	\N	100
bay:TayabasBay:1682656	S2	Tayabas Bay	bay	root	1	1033	2019-07-02 10:09:23.250889	\N	\N	100
state:KentrikiMakedonia:6697801	S2	Kentriki Makedonia	state	country:Greece:390903	1	5615	2019-06-30 17:59:35.7804	\N	\N	100
region:SpecialMunicipalities:	S2	Special Municipalities	region	country:Taiwan:1668284	0	1519	2019-06-30 11:03:24.523509	\N	\N	100
state:RennellAndBellona:7280293	S2	Rennell and Bellona	state	country:SolomonIslands:2103350	1	497	2019-07-03 05:02:35.340387	\N	\N	100
state:Panevezio:864480	S2	Panevezio	state	country:Lithuania:597427	1	3035	2019-07-01 18:51:40.682304	\N	\N	100
state:Shabwah:70935	S2	Shabwah	state	country:Yemen:69543	1	5474	2019-07-02 15:06:05.71247	\N	\N	100
state:Keguma:7628330	S2	Keguma	state	region:Riga:456173	1	1006	2019-07-01 18:56:22.824	\N	\N	100
state:Oberosterreich:2769848	S2	Oberösterreich	state	country:Austria:2782113	1	2563	2019-06-30 18:03:25.601225	\N	\N	100
country:Austria:2782113	S2	Austria	country	continent:Europe:6255148	0	14328	2019-06-30 18:01:32.313078	\N	\N	100
state:Causeni:618119	S2	Causeni	state	country:Moldova:617790	1	1032	2019-06-30 19:04:33.085404	\N	\N	100
state:Səki:828303	S2	Şəki	state	region:ShakiZaqatalaEconomicRegion:	1	997	2019-07-01 16:14:16.796284	\N	\N	100
state:Tuzla:3343716	S2	Tuzla	state	region:FederacijaBosnaIHercegovina:	1	1029	2019-07-04 16:30:46.819939	\N	\N	100
state:Mantova:3174050	S2	Mantova	state	region:Lombardia:3174618	1	1025	2019-07-02 01:53:17.893853	\N	\N	100
state:AlBayda:79838	S2	Al Bayda'	state	country:Yemen:69543	1	1237	2019-07-02 15:36:11.139951	\N	\N	100
state:Straseni:617301	S2	Străşeni	state	country:Moldova:617790	1	1029	2019-06-30 18:39:36.851785	\N	\N	100
state:Criuleni:617962	S2	Criuleni	state	country:Moldova:617790	1	1031	2019-06-30 18:39:36.852429	\N	\N	100
state:Amapa:3407762	S2	Amapá	state	country:Brazil:3469034	1	14191	2019-06-30 10:10:21.757498	\N	\N	100
state:Vlasenica:3294839	S2	Vlasenica	state	region:RepuplikaSrpska:	1	520	2019-07-04 16:30:46.818641	\N	\N	100
region:EmiliaRomagna:3177401	S2	Emilia-Romagna	region	country:Italy:3175395	0	3761	2019-06-30 18:01:32.862106	\N	\N	100
state:Mersin:311728	S2	Mersin	state	country:Turkey:298795	1	2553	2019-07-01 22:05:05.94219	\N	\N	100
day:26	S2	26	day	root	1	954885	2019-06-30 09:51:38.323584	\N	\N	100
state:Comrat:858895	S2	Comrat	state	country:Moldova:617790	1	2056	2019-06-30 19:02:31.053578	\N	\N	100
state:Abyan:80425	S2	Abyan	state	country:Yemen:69543	1	3214	2019-07-02 16:27:24.019064	\N	\N	100
sea:AegeanSea:265695	S2	Aegean Sea	sea	root	1	20674	2019-06-30 17:59:35.779941	\N	\N	100
region:Macvanski:7581799	S2	Mačvanski	region	country:Serbia:6290252	0	1018	2019-07-01 18:58:33.642268	\N	\N	100
state:Dosso:2445486	S2	Dosso	state	country:Niger:2440476	1	4263	2019-06-30 17:43:44.481109	\N	\N	100
state:Vecumnieku:7628329	S2	Vecumnieku	state	region:Zemgale:7639660	1	1005	2019-07-01 18:56:22.824429	\N	\N	100
state:Omnogovi:2029669	S2	Ömnögovi	state	country:Mongolia:2029969	1	25724	2019-06-30 13:33:24.087261	\N	\N	100
state:Taraclia:617264	S2	Taraclia	state	country:Moldova:617790	1	2060	2019-06-30 19:02:31.054934	\N	\N	100
state:CentralFinland:830685	S2	Central Finland	state	country:Finland:660013	1	5845	2019-06-30 18:15:58.493008	\N	\N	100
state:Zabul:1121143	S2	Zabul	state	country:Afghanistan:1149361	1	3505	2019-07-01 15:25:07.564543	\N	\N	100
state:Astana:1538317	S2	Astana	state	country:Kazakhstan:1522867	1	1004	2019-07-02 14:24:02.042577	\N	\N	100
state:Leninabad:1221092	S2	Leninabad	state	region:Khudzhand:1514879	1	3356	2019-07-01 15:17:03.21059	\N	\N	100
state:Kermanshah:128222	S2	Kermanshah	state	country:Iran:130758	1	4479	2019-06-30 19:21:36.597511	\N	\N	100
state:Bologna:3181927	S2	Bologna	state	region:EmiliaRomagna:3177401	1	509	2019-07-03 17:38:32.603006	\N	\N	100
state:Sind:1164807	S2	Sind	state	country:Pakistan:1168579	1	16062	2019-06-30 18:02:28.951585	\N	\N	100
state:AneniiNoi:617715	S2	Anenii Noi	state	country:Moldova:617790	1	2058	2019-06-30 18:39:36.849828	\N	\N	100
state:AlBahah:109954	S2	Al Bahah	state	country:SaudiArabia:102358	1	1010	2019-07-03 15:45:41.930817	\N	\N	100
state:Aqmola:1518003	S2	Aqmola	state	country:Kazakhstan:1522867	1	28475	2019-06-30 14:41:41.939628	\N	\N	100
state:Basarabeasca:618565	S2	Basarabeasca	state	country:Moldova:617790	1	1029	2019-06-30 19:04:33.084502	\N	\N	100
state:Mangghystau:608879	S2	Mangghystau	state	country:Kazakhstan:1522867	1	24051	2019-06-30 19:49:42.086582	\N	\N	100
state:Ouargla:2485794	S2	Ouargla	state	country:Algeria:2589581	1	24726	2019-06-30 17:58:29.941437	\N	\N	100
state:KabardinBalkar:	S2	Kabardin-Balkar	state	region:Volga:11961325	1	2011	2019-07-02 17:01:46.157764	\N	\N	100
state:KhmelNytsKyy:706369	S2	Khmel'nyts'kyy	state	country:Ukraine:690791	1	3653	2019-06-30 18:44:36.643621	\N	\N	100
day:29	S2	29	day	root	1	903683	2019-06-30 09:51:43.533768	\N	\N	100
state:Kolubarski:7581798	S2	Kolubarski	state	country:Serbia:6290252	1	3054	2019-07-01 18:36:47.85271	\N	\N	100
state:Talas:1527297	S2	Talas	state	country:Kyrgyzstan:1527747	1	3501	2019-07-01 15:16:39.183574	\N	\N	100
state:Agri:325163	S2	Agri	state	country:Turkey:298795	1	1984	2019-07-01 16:41:58.950552	\N	\N	100
state:NorthKarelia:641200	S2	North Karelia	state	country:Finland:660013	1	8607	2019-06-30 10:10:21.324591	\N	\N	100
state:Sirdaryo:1484840	S2	Sirdaryo	state	country:Uzbekistan:1512440	1	1979	2019-07-01 15:17:03.212338	\N	\N	100
state:Morbihan:2991879	S2	Morbihan	state	region:Bretagne:3030293	1	1024	2019-07-01 19:47:48.106949	\N	\N	100
state:RachaLechkhumiKvemoSvaneti:865542	S2	Racha-Lechkhumi-Kvemo Svaneti	state	country:Georgia:614540	1	1007	2019-07-02 17:21:54.684583	\N	\N	100
state:AlMuharraq:290332	S2	Al Muḩarraq	state	country:Bahrain:290291	1	524	2019-07-04 14:44:08.143628	\N	\N	100
state:EastAzarbaijan:142549	S2	East Azarbaijan	state	country:Iran:130758	1	5623	2019-06-30 20:10:43.714926	\N	\N	100
state:Kursk:538555	S2	Kursk	state	region:Central:11961322	1	5685	2019-07-01 19:41:18.632969	\N	\N	100
region:Khudzhand:1514879	S2	Khudzhand	region	country:Tajikistan:1220409	0	3355	2019-07-01 15:17:03.211286	\N	\N	100
region:Bretagne:3030293	S2	Bretagne	region	country:France:3017382	0	5051	2019-07-01 19:13:17.165869	\N	\N	100
state:Rajasthan:1258899	S2	Rajasthan	state	region:Central:8335421	1	34986	2019-06-30 16:19:03.038705	\N	\N	100
region:Rasinski:7581806	S2	Rasinski	region	country:Serbia:6290252	0	1003	2019-07-01 18:52:42.494809	\N	\N	100
state:Qaraghandy:1523401	S2	Qaraghandy	state	country:Kazakhstan:1522867	1	60887	2019-06-30 14:40:40.94348	\N	\N	100
country:Rwanda:49518	S2	Rwanda	country	continent:Africa:6255146	0	3153	2019-07-02 15:32:40.759155	\N	\N	100
state:Diyala:96965	S2	Diyala	state	region:Iraq:99237	1	1667	2019-07-03 16:43:49.663708	\N	\N	100
state:Sumadijski:7581908	S2	Šumadijski	state	country:Serbia:6290252	1	1554	2019-07-01 18:36:47.851763	\N	\N	100
state:Hovsgol:2030469	S2	Hövsgöl	state	country:Mongolia:2029969	1	18053	2019-06-30 13:37:12.340354	\N	\N	100
state:Tanintharyi:1293118	S2	Tanintharyi	state	country:Myanmar:1327865	1	5903	2019-07-01 14:51:04.866655	\N	\N	100
state:Equateur:216661	S2	Équateur	state	country:Congo:203312	1	35538	2019-07-01 23:02:12.912579	\N	\N	100
state:Kemerovo:1503900	S2	Kemerovo	state	region:Siberian:11961345	1	11969	2019-07-01 15:46:17.59106	\N	\N	100
state:Batanes:1726302	S2	Batanes	state	region:CagayanValley(RegionIi):	1	1015	2019-07-02 10:19:00.606014	\N	\N	100
country:Gabon:2400553	S2	Gabon	country	continent:Africa:6255146	0	24298	2019-06-30 18:18:40.023248	\N	\N	100
state:ElOued:2497406	S2	El Oued	state	country:Algeria:2589581	1	6688	2019-06-30 18:23:46.243515	\N	\N	100
state:Pomoravski:7581805	S2	Pomoravski	state	region:Rasinski:7581806	1	1196	2019-07-01 18:52:42.494306	\N	\N	100
region:NationalCapitalRegion:7521311	S2	National Capital Region	region	country:Philippines:1694008	0	531	2019-07-02 09:31:11.652235	\N	\N	100
state:Arusha:161322	S2	Arusha	state	country:Tanzania:149590	1	5918	2019-07-01 16:57:13.609496	\N	\N	100
state:ShamalSina:349401	S2	Shamal Sina'	state	country:Egypt:357994	1	3482	2019-06-30 19:04:11.99301	\N	\N	100
state:GradBeograd:8356015	S2	Grad Beograd	state	country:Serbia:6290252	1	1020	2019-07-01 18:36:47.854105	\N	\N	100
state:Van:298113	S2	Van	state	country:Turkey:298795	1	3614	2019-07-01 16:41:58.951053	\N	\N	100
state:Astrakhan:580491	S2	Astrakhan'	state	region:Volga:11961325	1	6701	2019-07-01 15:42:27.928885	\N	\N	100
sound:AlbemarleSound:4452317	S2	Albemarle Sound	sound	root	1	3690	2019-07-01 02:09:26.153348	\N	\N	100
state:WestAzarbaijan:142550	S2	West Azarbaijan	state	country:Iran:130758	1	4576	2019-07-01 16:13:40.986693	\N	\N	100
state:Adiyaman:325329	S2	Adiyaman	state	country:Turkey:298795	1	2549	2019-06-30 19:11:46.608414	\N	\N	100
state:Bulacan:1723064	S2	Bulacan	state	region:CentralLuzon(RegionIii):	1	1011	2019-07-02 09:31:11.653496	\N	\N	100
state:AsSulaymaniyah:98465	S2	As-Sulaymaniyah	state	region:Kurdistan:8429352	1	2799	2019-07-01 16:31:02.758779	\N	\N	100
state:QuezonCity:1692193	S2	Quezon City	state	region:NationalCapitalRegion:7521311	1	507	2019-07-02 09:31:11.651525	\N	\N	100
state:Borski:7581582	S2	Borski	state	country:Serbia:6290252	1	3048	2019-07-01 19:04:21.274908	\N	\N	100
state:Bryansk:824997	S2	Bryansk	state	region:Central:11961322	1	5389	2019-06-30 17:33:58.399535	\N	\N	100
state:Odessa:698738	S2	Odessa	state	country:Ukraine:690791	1	7528	2019-06-30 18:41:05.359382	\N	\N	100
state:PrachuapKhiriKhan:1151073	S2	Prachuap Khiri Khan	state	region:Western:6470693	1	993	2019-07-01 14:51:04.867703	\N	\N	100
state:Elazig:315807	S2	Elazig	state	country:Turkey:298795	1	1539	2019-07-02 16:29:30.021569	\N	\N	100
state:NakhonSawan:1608526	S2	Nakhon Sawan	state	region:Central:10177180	1	1026	2019-07-01 15:09:06.40653	\N	\N	100
state:Podunavski:7581804	S2	Podunavski	state	country:Serbia:6290252	1	1001	2019-07-01 18:36:47.852258	\N	\N	100
state:SumateraUtara:1213642	S2	Sumatera Utara	state	country:Indonesia:1643084	1	8209	2019-07-01 14:22:30.449943	\N	\N	100
state:Okayama:1854381	S2	Okayama	state	region:Chugoku:1864492	1	1026	2019-07-03 08:34:56.434113	\N	\N	100
state:Uvs:1514967	S2	Uvs	state	country:Mongolia:2029969	1	12470	2019-06-30 15:32:27.240004	\N	\N	100
state:Ngounie:2397466	S2	Ngounié	state	country:Gabon:2400553	1	5854	2019-07-01 18:51:39.761569	\N	\N	100
state:Liberecky:3339541	S2	Liberecký	state	country:CzechRepublic:3077311	1	1948	2019-06-30 18:18:16.406414	\N	\N	100
state:Berea:932932	S2	Berea	state	country:Lesotho:932692	1	1040	2019-07-01 17:19:18.307986	\N	\N	100
state:Quezon:1692194	S2	Quezon	state	region:Calabarzon(RegionIvA):	1	2049	2019-06-30 14:09:28.159091	\N	\N	100
state:Peloponnisos:6697807	S2	Peloponnisos	state	region:Peloponnisos:6697807	1	2566	2019-06-30 18:43:40.986458	\N	\N	100
state:Vinnytsya:689558	S2	Vinnytsya	state	country:Ukraine:690791	1	4653	2019-06-30 18:44:36.826613	\N	\N	100
state:Razgrad:453751	S2	Razgrad	state	country:Bulgaria:732800	1	514	2019-06-30 18:44:38.817571	\N	\N	100
state:Balti:873909	S2	Bălţi	state	country:Moldova:617790	1	1027	2019-06-30 18:45:01.710283	\N	\N	100
state:Nigde:303826	S2	Nigde	state	country:Turkey:298795	1	1041	2019-07-03 15:18:53.151544	\N	\N	100
state:KalimantanBarat:1641900	S2	Kalimantan Barat	state	country:Indonesia:1643084	1	13412	2019-07-01 13:01:53.710464	\N	\N	100
state:Blagoevgrad:733192	S2	Blagoevgrad	state	country:Bulgaria:732800	1	2563	2019-06-30 18:21:17.14781	\N	\N	100
state:CentralOstrobothnia:830675	S2	Central Ostrobothnia	state	country:Finland:660013	1	2563	2019-06-30 17:59:25.79026	\N	\N	100
state:QachaSNek:932219	S2	Qacha's Nek	state	country:Lesotho:932692	1	1392	2019-07-01 17:07:35.305142	\N	\N	100
state:Pampanga:1695848	S2	Pampanga	state	region:CentralLuzon(RegionIii):	1	290	2019-07-07 12:08:02.01142	\N	\N	100
state:Leribe:932700	S2	Leribe	state	country:Lesotho:932692	1	1551	2019-07-01 16:37:43.109316	\N	\N	100
state:Kauno:864477	S2	Kauno	state	country:Lithuania:597427	1	4544	2019-07-01 18:36:15.381526	\N	\N	100
state:Nangarhar:1132366	S2	Nangarhar	state	country:Afghanistan:1149361	1	1506	2019-07-01 15:23:29.411455	\N	\N	100
region:Kurdistan:8429352	S2	Kurdistan	region	country:Iraq:99237	0	6301	2019-07-01 16:19:47.677736	\N	\N	100
state:Pomoravski:7581805	S2	Pomoravski	state	country:Serbia:6290252	1	307	2022-05-06 16:39:05.285718	\N	\N	100
state:Ruyigi:426699	S2	Ruyigi	state	country:Burundi:433561	1	2269	2019-07-02 15:50:07.689742	\N	\N	100
state:LowerSilesian:3337492	S2	Lower Silesian	state	country:Poland:798544	1	3386	2019-06-30 18:15:10.043054	\N	\N	100
state:Toplicki:7581909	S2	Toplicki	state	region:Toplicki:7581909	1	503	2019-07-01 18:31:03.029028	\N	\N	100
state:EntreRios:3434137	S2	Entre Ríos	state	country:Argentina:3865483	1	11259	2019-07-02 05:40:03.361585	\N	\N	100
state:Nevsehir:303830	S2	Nevsehir	state	country:Turkey:298795	1	1040	2019-07-03 15:18:53.15079	\N	\N	100
state:Ruse:453751	S2	Ruse	state	country:Bulgaria:732800	1	1029	2019-06-30 18:40:16.557429	\N	\N	100
state:Rizal:1691591	S2	Rizal	state	region:Calabarzon(RegionIvA):	1	509	2019-07-02 09:31:11.652911	\N	\N	100
state:Zajecarski:7581910	S2	Zajecarski	state	region:Zajecarski:7581910	1	2025	2019-07-01 19:04:21.277159	\N	\N	100
region:Calabarzon(RegionIvA):	S2	CALABARZON (Region IV-A)	region	country:Philippines:1694008	0	2621	2019-06-30 14:09:28.159548	\N	\N	100
state:DarA:170903	S2	Dar`a	state	country:Syria:163843	1	2040	2019-06-30 19:05:32.931946	\N	\N	100
sea:WeddellSea:4036624	S2	Weddell Sea	sea	root	1	136995	2019-09-02 19:58:12.084971	\N	\N	100
sound:PamlicoSound:4483906	S2	Pamlico Sound	sound	root	1	3067	2019-07-01 02:09:26.153788	\N	\N	100
state:AtTaMim:98410	S2	At-Ta'mim	state	region:Iraq:99237	1	991	2019-07-01 16:34:07.246334	\N	\N	100
state:CamarinesNorte:1719846	S2	Camarines Norte	state	region:Bicol(RegionV):	1	1022	2019-06-30 14:09:28.159986	\N	\N	100
state:Volgograd:472755	S2	Volgograd	state	region:Volga:11961325	1	17616	2019-06-30 19:02:12.255716	\N	\N	100
state:MaAn:248380	S2	Ma`an	state	country:Jordan:248816	1	6183	2019-06-30 19:07:32.186297	\N	\N	100
state:Lautem:1638294	S2	Lautém	state	country:TimorLeste:1966436	1	506	2019-06-30 10:33:56.621547	\N	\N	100
country:Burundi:433561	S2	Burundi	country	continent:Africa:6255146	0	5180	2019-07-02 15:49:37.404204	\N	\N	100
strait:LaPerouseStrait:2123847	S2	La Pérouse Strait	strait	root	1	1025	2019-06-30 10:14:22.491101	\N	\N	100
state:North:7533617	S2	North	state	region:TheNewTerritories:	1	1995	2019-07-01 12:24:26.864288	\N	\N	100
state:AnNajaf:98862	S2	An-Najaf	state	region:Iraq:99237	1	4464	2019-07-01 16:11:56.561979	\N	\N	100
region:Toplicki:7581909	S2	Toplički	region	country:Serbia:6290252	0	503	2019-07-01 18:31:03.029625	\N	\N	100
region:Zajecarski:7581910	S2	Zaječarski	region	country:Serbia:6290252	0	2025	2019-07-01 19:04:21.277624	\N	\N	100
country:Kosovo:831053	S2	Kosovo	country	continent:Europe:6255148	0	1523	2019-07-01 18:31:03.027626	\N	\N	100
state:Plovdiv:728194	S2	Plovdiv	state	country:Bulgaria:732800	1	4097	2019-06-30 18:21:17.14716	\N	\N	100
state:Kalmyk:553972	S2	Kalmyk	state	region:Volga:11961325	1	6541	2019-07-01 15:40:31.423803	\N	\N	100
state:Mbarara:229268	S2	Mbarara	state	region:Western:8260675	1	517	2019-07-02 16:40:46.975437	\N	\N	100
state:AsSuwayda:172410	S2	As Suwayda'	state	country:Syria:163843	1	2041	2019-06-30 19:03:13.676417	\N	\N	100
state:Baucau:1649538	S2	Baucau	state	country:TimorLeste:1966436	1	1010	2019-06-30 10:33:37.755625	\N	\N	100
state:Asyut:359781	S2	Asyut	state	country:Egypt:357994	1	1556	2019-07-03 12:53:16.620739	\N	\N	100
state:SouthernOstrobothnia:830682	S2	Southern Ostrobothnia	state	country:Finland:660013	1	5687	2019-06-30 17:59:25.789835	\N	\N	100
state:Kaolack:2250804	S2	Kaolack	state	country:Senegal:2245662	1	2290	2019-07-02 20:11:50.634897	\N	\N	100
state:Pazardzhik:728379	S2	Pazardzhik	state	country:Bulgaria:732800	1	3079	2019-06-30 18:21:17.14861	\N	\N	100
state:Mokhotlong:932418	S2	Mokhotlong	state	country:Lesotho:932692	1	2069	2019-07-01 16:32:25.499426	\N	\N	100
state:Guangdong:1809935	S2	Guangdong	state	region:SouthCentralChina:	1	21134	2019-07-01 11:52:32.505846	\N	\N	100
state:Suhaj:347794	S2	Suhaj	state	country:Egypt:357994	1	531	2019-07-03 12:53:17.246689	\N	\N	100
country:Eritrea:338010	S2	Eritrea	country	continent:Africa:6255146	0	14649	2019-06-30 20:45:35.000453	\N	\N	100
state:Nordjylland:6418540	S2	Nordjylland	state	country:Denmark:2623032	1	2060	2019-06-30 21:13:32.329083	\N	\N	100
sea:ArabianSea:1158455	S2	Arabian Sea	sea	root	1	137307	2019-06-30 15:45:26.066761	\N	\N	100
state:KomaromEsztergom:3049518	S2	Komárom-Esztergom	state	region:CentralTransdanubia:	1	1047	2019-07-02 17:55:35.951883	\N	\N	100
state:Karlovarsky:3339539	S2	Karlovarský	state	country:CzechRepublic:3077311	1	2002	2019-07-02 01:40:26.168858	\N	\N	100
state:Maharashtra:1264418	S2	Maharashtra	state	region:West:1252881	1	31325	2019-07-01 17:20:22.548091	\N	\N	100
state:Oshikoto:3371208	S2	Oshikoto	state	country:Namibia:3355338	1	6132	2019-06-30 09:51:38.328416	\N	\N	100
state:Kunene:3371202	S2	Kunene	state	country:Namibia:3355338	1	13596	2019-06-30 10:10:55.511599	\N	\N	100
state:MaRib:72966	S2	Ma'rib	state	country:Yemen:69543	1	1396	2019-07-02 14:43:39.790266	\N	\N	100
state:NorthBank:2412353	S2	North Bank	state	country:Gambia:2413451	1	3058	2019-07-02 20:19:27.835013	\N	\N	100
state:Nisporeni:617754	S2	Nisporeni	state	country:Moldova:617790	1	1037	2019-06-30 18:45:01.713786	\N	\N	100
state:KasaiOccidental:214139	S2	Kasaï-Occidental	state	country:Congo:203312	1	15980	2019-07-01 23:11:48.307118	\N	\N	100
state:Khakass:1503834	S2	Khakass	state	region:Siberian:11961345	1	11610	2019-06-30 12:56:23.489082	\N	\N	100
state:Baghlan:1147537	S2	Baghlan	state	country:Afghanistan:1149361	1	1496	2019-07-01 15:16:19.347667	\N	\N	100
state:Limbazi:457890	S2	Limbaži	state	region:Riga:456173	1	1028	2019-07-02 18:08:39.549813	\N	\N	100
state:Camenca:618453	S2	Camenca	state	country:Moldova:617790	1	1041	2019-06-30 19:08:31.901662	\N	\N	100
state:Pirkanmaa:830704	S2	Pirkanmaa	state	country:Finland:660013	1	5165	2019-06-30 18:04:47.07742	\N	\N	100
state:ThabaTseka:932011	S2	Thaba-Tseka	state	country:Lesotho:932692	1	1091	2019-07-01 17:46:52.019508	\N	\N	100
region:Vidzeme:7639661	S2	Vidzeme	region	country:Latvia:458258	0	2885	2019-06-30 10:10:32.19005	\N	\N	100
state:AlMinya:360688	S2	Al Minya	state	country:Egypt:357994	1	4141	2019-07-01 21:24:47.726972	\N	\N	100
state:Viqueque:1622470	S2	Viqueque	state	country:TimorLeste:1966436	1	1014	2019-06-30 10:33:37.757873	\N	\N	100
state:Ungheni:617181	S2	Ungheni	state	country:Moldova:617790	1	530	2019-06-30 18:45:01.714975	\N	\N	100
country:TimorLeste:1966436	S2	Timor-Leste	country	continent:Asia:6255147	0	1541	2019-06-30 10:33:37.758509	\N	\N	100
state:Abra:1732380	S2	Abra	state	region:CordilleraAdministrativeRegion(Car):	1	2040	2019-06-30 11:08:07.468967	\N	\N	100
country:Lesotho:932692	S2	Lesotho	country	continent:Africa:6255146	0	3815	2019-07-01 16:32:25.500164	\N	\N	100
state:Glodeni:618260	S2	Glodeni	state	country:Moldova:617790	1	1030	2019-06-30 18:45:01.713121	\N	\N	100
state:AnatolikiMakedoniaKaiThraki:6697803	S2	Anatoliki Makedonia kai Thraki	state	country:Greece:390903	1	4131	2019-06-30 17:59:35.78086	\N	\N	100
state:Singerei:617913	S2	Sîngerei	state	country:Moldova:617790	1	1556	2019-06-30 18:45:01.71438	\N	\N	100
state:Jambi:1642856	S2	Jambi	state	country:Indonesia:1643084	1	5121	2019-06-30 11:54:23.345481	\N	\N	100
state:Smolyan:864558	S2	Smolyan	state	country:Bulgaria:732800	1	4087	2019-06-30 18:21:17.148211	\N	\N	100
state:WarmianMasurian:858791	S2	Warmian-Masurian	state	country:Poland:798544	1	4929	2019-06-30 18:18:57.05545	\N	\N	100
country:Denmark:2623032	S2	Denmark	country	continent:Europe:6255148	0	10392	2019-06-30 09:51:58.620507	\N	\N	100
state:Soldanesti:858808	S2	Şoldăneşti	state	country:Moldova:617790	1	2075	2019-06-30 18:39:36.846017	\N	\N	100
state:Phitsanulok:1607707	S2	Phitsanulok	state	region:Central:10177180	1	2051	2019-07-01 14:28:21.182909	\N	\N	100
state:Ohangwena:3371203	S2	Ohangwena	state	country:Namibia:3355338	1	1529	2019-06-30 10:10:55.709084	\N	\N	100
state:DebubawiKeyihBahri:448499	S2	Debubawi Keyih Bahri	state	country:Eritrea:338010	1	3642	2019-06-30 20:45:34.999889	\N	\N	100
state:Soroca:617366	S2	Soroca	state	country:Moldova:617790	1	2043	2019-06-30 19:04:46.041608	\N	\N	100
state:Amatas:7628375	S2	Amatas	state	region:Vidzeme:7639661	1	1008	2019-06-30 10:10:35.68032	\N	\N	100
state:NorthOssetia:519969	S2	North Ossetia	state	region:Volga:11961325	1	996	2019-07-04 13:19:37.5575	\N	\N	100
state:Isabela:1710515	S2	Isabela	state	region:CagayanValley(RegionIi):	1	1042	2019-07-02 10:12:47.865662	\N	\N	100
state::	S2	_all	state	country:Antarctica:6632699	1	550	2022-09-10 20:56:13.820744	\N	\N	100
state:EasternCape:1085593	S2	Eastern Cape	state	country:SouthAfrica:953987	1	21242	2019-07-01 16:37:42.620252	\N	\N	100
state:MtskhetaMtianeti:865541	S2	Mtskheta-Mtianeti	state	country:Georgia:614540	1	2529	2019-07-01 16:19:47.500541	\N	\N	100
state:Falesti:618345	S2	Făleşti	state	country:Moldova:617790	1	515	2019-06-30 18:45:01.715554	\N	\N	100
state:Waikato:2180293	S2	Waikato	state	region:NorthIsland:2185983	1	3482	2019-07-01 07:42:43.916966	\N	\N	100
state:Kunduz:1135690	S2	Kunduz	state	country:Afghanistan:1149361	1	1958	2019-07-01 15:16:19.34809	\N	\N	100
state:Banskobystricky:3343954	S2	Banskobystrický	state	country:Slovakia:3057568	1	3404	2019-07-02 17:54:46.06006	\N	\N	100
state:Zanjan:111452	S2	Zanjan	state	country:Iran:130758	1	4632	2019-06-30 20:00:36.143931	\N	\N	100
state:Calarasi:618162	S2	Călărași	state	country:Moldova:617790	1	2063	2019-06-30 18:39:36.847278	\N	\N	100
state:Plzensky:3339575	S2	Plzeňský	state	country:CzechRepublic:3077311	1	3035	2019-07-02 01:40:26.168138	\N	\N	100
state:Pahang:1733042	S2	Pahang	state	country:Malaysia:1733045	1	6127	2019-06-30 12:43:11.012368	\N	\N	100
country:SouthKorea:1835841	S2	South Korea	country	continent:Asia:6255147	0	13191	2019-07-01 03:39:22.58429	\N	\N	100
state:LundaSul:145701	S2	Lunda Sul	state	country:Angola:3351879	1	9263	2019-06-30 09:51:40.565321	\N	\N	100
state:Briceni:617077	S2	Briceni	state	country:Moldova:617790	1	2054	2019-06-30 18:40:28.960426	\N	\N	100
state:Botosani:684038	S2	Botosani	state	country:Romania:798549	1	2578	2019-06-30 18:40:28.961083	\N	\N	100
state:Talsi:454968	S2	Talsi	state	region:Kurzeme:460496	1	1534	2019-07-02 18:08:21.556333	\N	\N	100
state:IlocosSur:1711032	S2	Ilocos Sur	state	region:Ilocos(RegionI):	1	2562	2019-06-30 11:08:07.467699	\N	\N	100
state:PulauPinang:1733047	S2	Pulau Pinang	state	country:Malaysia:1733045	1	1977	2019-07-01 14:26:56.921226	\N	\N	100
state:GoviAltay:1515917	S2	Govi-Altay	state	country:Mongolia:2029969	1	22592	2019-07-01 14:04:23.242516	\N	\N	100
state:Afar:444179	S2	Afar	state	country:Ethiopia:337996	1	8284	2019-06-30 20:34:56.713116	\N	\N	100
state:Novgorod:519324	S2	Novgorod	state	region:Northwestern:11961321	1	10974	2019-06-30 10:14:20.232193	\N	\N	100
state:Takhar:1123230	S2	Takhar	state	country:Afghanistan:1149361	1	2138	2019-07-01 15:18:05.238	\N	\N	100
state:Xizang:1279685	S2	Xizang	state	region:SouthwestChina:	1	122072	2019-06-30 10:15:40.367892	\N	\N	100
state:JaszNagykunSzolnok:719637	S2	Jász-Nagykun-Szolnok	state	region:NorthernGreatPlain:	1	1028	2019-07-01 18:53:33.294352	\N	\N	100
state:JalalAbad:1529778	S2	Jalal-Abad	state	country:Kyrgyzstan:1527747	1	7512	2019-06-30 16:38:42.591207	\N	\N	100
state:SumateraSelatan:1626196	S2	Sumatera Selatan	state	country:Indonesia:1643084	1	8806	2019-06-30 11:56:12.071724	\N	\N	100
state:Kokneses:7628357	S2	Kokneses	state	region:Zemgale:7639660	1	1007	2019-07-01 18:57:20.91159	\N	\N	100
state:Uttaradit:1605214	S2	Uttaradit	state	region:Northern:6695803	1	1550	2019-07-01 14:28:21.181882	\N	\N	100
state:Kavango:3371204	S2	Kavango	state	country:Namibia:3355338	1	5147	2019-06-30 09:51:38.927345	\N	\N	100
region:Northern:6695803	S2	Northern	region	country:Thailand:1605651	0	11827	2019-06-30 10:14:46.672093	\N	\N	100
state:SaKaeo:1906691	S2	Sa Kaeo	state	region:Eastern:7114724	1	2526	2019-07-01 14:28:28.636758	\N	\N	100
state:Chernivtsi:710720	S2	Chernivtsi	state	country:Ukraine:690791	1	1551	2019-06-30 18:40:28.962238	\N	\N	100
state:Andijon:1484846	S2	Andijon	state	country:Uzbekistan:1512440	1	1986	2019-07-01 15:22:37.573876	\N	\N	100
state:Ryazan:500059	S2	Ryazan'	state	region:Central:11961322	1	4907	2019-06-30 19:13:30.503388	\N	\N	100
state:Namangan:1484842	S2	Namangan	state	country:Uzbekistan:1512440	1	2966	2019-07-01 15:22:37.574359	\N	\N	100
state:Tver:480041	S2	Tver'	state	region:Central:11961322	1	15224	2019-06-30 17:31:28.710757	\N	\N	100
state:Rugaju:7628352	S2	Rugaju	state	region:Latgale:7639662	1	1018	2019-06-30 10:10:33.071323	\N	\N	100
state:Gulbene:459668	S2	Gulbene	state	region:Vidzeme:7639661	1	1009	2019-06-30 10:10:33.07174	\N	\N	100
state:Rostov:501165	S2	Rostov	state	region:Volga:11961325	1	15491	2019-06-30 19:01:39.890755	\N	\N	100
state:Bali:1650535	S2	Bali	state	country:Indonesia:1643084	1	1711	2019-06-30 12:13:07.817469	\N	\N	100
state:StaraZagora:864559	S2	Stara Zagora	state	country:Bulgaria:732800	1	1032	2019-06-30 19:08:55.97211	\N	\N	100
state:CapitalTerritory(Honiara):9259067	S2	Capital Territory (Honiara)	state	country:SolomonIslands:2103350	1	1994	2019-07-01 09:01:46.926565	\N	\N	100
state:Guadalcanal:2108831	S2	Guadalcanal	state	country:SolomonIslands:2103350	1	1996	2019-07-01 09:01:46.927072	\N	\N	100
state:Benghazi:88318	S2	Benghazi	state	country:Libya:2215636	1	2044	2019-06-30 19:03:56.385152	\N	\N	100
state:Gabrovo:864552	S2	Gabrovo	state	country:Bulgaria:732800	1	1025	2019-06-30 19:09:59.422771	\N	\N	100
state:Lovech:729560	S2	Lovech	state	country:Bulgaria:732800	1	2053	2019-06-30 18:45:28.78994	\N	\N	100
state:BayOfPlenty:2182560	S2	Bay of Plenty	state	region:NorthIsland:2185983	1	1952	2019-07-01 07:44:44.661264	\N	\N	100
region:CapitalRegion:2800867	S2	Capital Region	region	country:Belgium:2802361	0	1014	2019-06-30 21:09:19.7263	\N	\N	100
state:Chaiyaphum:1611406	S2	Chaiyaphum	state	region:Northeastern:	1	1013	2019-07-01 14:20:55.661572	\N	\N	100
state:Eastern:6413337	S2	Eastern	state	country:Rwanda:49518	1	2099	2019-07-02 15:32:40.758757	\N	\N	100
state:Debub:448502	S2	Debub	state	country:Eritrea:338010	1	3674	2019-07-01 16:19:47.335197	\N	\N	100
state:Orissa:1261029	S2	Orissa	state	region:East:1272123	1	19418	2019-06-30 15:21:18.812572	\N	\N	100
state:Phetchabun:1607736	S2	Phetchabun	state	region:Central:10177180	1	1003	2019-07-01 14:20:55.660596	\N	\N	100
state:AyionOros:736572	S2	Ayion Oros	state	region:Macedonia:6697801	1	2050	2019-06-30 18:46:01.646127	\N	\N	100
state:Benguet:1725582	S2	Benguet	state	region:CordilleraAdministrativeRegion(Car):	1	2044	2019-06-30 11:03:43.788513	\N	\N	100
bay:BayOfPlenty:2182560	S2	Bay of Plenty	bay	root	1	2434	2019-07-01 07:44:44.660771	\N	\N	100
region:Riga:456173	S2	Riga	region	country:Latvia:458258	0	2579	2019-06-30 10:10:35.679787	\N	\N	100
state:Badakhshan:1147745	S2	Badakhshan	state	country:Afghanistan:1149361	1	9191	2019-06-30 16:17:23.430151	\N	\N	100
state:Haskovo:730436	S2	Haskovo	state	country:Bulgaria:732800	1	1030	2019-06-30 19:08:55.973333	\N	\N	100
state:SatuMare:667869	S2	Satu Mare	state	country:Romania:798549	1	1014	2019-07-01 19:00:22.864629	\N	\N	100
state:Salaj:668248	S2	Salaj	state	country:Romania:798549	1	1018	2019-07-01 19:03:29.076642	\N	\N	100
sea:SeaOfAzov:713066	S2	Sea of Azov	sea	root	1	9250	2019-06-30 19:01:24.854091	\N	\N	100
region:Macedonia:6697801	S2	Macedonia	region	country:Greece:390903	0	2050	2019-06-30 18:46:01.646607	\N	\N	100
state:Nuristan:1444363	S2	Nuristan	state	country:Afghanistan:1149361	1	1980	2019-07-01 15:18:05.239007	\N	\N	100
state:SouthernDarfur:408660	S2	Southern Darfur	state	region:Darfur:408660	1	7935	2019-07-01 21:28:17.083225	\N	\N	100
day:09	S2	09	day	root	1	918294	2019-07-09 09:06:50.474177	\N	\N	100
state:Yambol:864563	S2	Yambol	state	country:Bulgaria:732800	1	2072	2019-06-30 18:47:04.680972	\N	\N	100
region:GubaKhachmazEconomicRegion:	S2	Guba-Khachmaz Economic Region	region	country:Azerbaijan:587116	0	2935	2019-06-30 19:55:09.527095	\N	\N	100
state:TamilNadu:1255053	S2	Tamil Nadu	state	region:South:8335041	1	14088	2019-06-30 15:19:05.218498	\N	\N	100
state:Cherkasy:710802	S2	Cherkasy	state	country:Ukraine:690791	1	5467	2019-06-30 18:43:38.044266	\N	\N	100
state:Arhangay:2032855	S2	Arhangay	state	country:Mongolia:2029969	1	10377	2019-06-30 13:31:53.861593	\N	\N	100
state:MarquesasIslands:4019977	S2	Marquesas Islands	state	country:FrenchPolynesia:4030656	1	1006	2019-07-01 04:36:06.713502	\N	\N	100
state:Pleven:728204	S2	Pleven	state	country:Bulgaria:732800	1	2063	2019-06-30 18:40:16.558811	\N	\N	100
state:GisborneDistrict:2190767	S2	Gisborne District	state	region:NorthIsland:2185983	1	2427	2019-07-01 07:47:11.395417	\N	\N	100
state:Bulgan:2032199	S2	Bulgan	state	country:Mongolia:2029969	1	11720	2019-06-30 13:30:28.829475	\N	\N	100
region:FarWestern:7289705	S2	Far-Western	region	country:Nepal:1282988	0	4505	2019-07-01 16:05:31.238177	\N	\N	100
state:Zhambyl:1524444	S2	Zhambyl	state	country:Kazakhstan:1522867	1	20239	2019-06-30 16:42:08.509484	\N	\N	100
state:BiokoNorte:2566978	S2	Bioko Norte	state	country:EquatorialGuinea:2309096	1	1014	2019-07-04 15:43:51.87262	\N	\N	100
state:Arges:686192	S2	Arges	state	country:Romania:798549	1	4111	2019-06-30 18:09:25.09912	\N	\N	100
state:Paktika:1131256	S2	Paktika	state	country:Afghanistan:1149361	1	3006	2019-07-01 15:24:28.830196	\N	\N	100
state:Vakaga:236178	S2	Vakaga	state	country:CentralAfricanRepublic:239880	1	5111	2019-07-01 21:47:43.37984	\N	\N	100
day:31	S2	31	day	root	1	564660	2019-07-31 02:31:53.532799	\N	\N	100
state:Dambovita:679385	S2	Dâmbovita	state	country:Romania:798549	1	2567	2019-06-30 18:09:25.097815	\N	\N	100
state:NordTrondelag:3144148	S2	Nord-Trøndelag	state	country:Norway:3144096	1	6445	2019-06-30 21:15:16.550988	\N	\N	100
state:Bengkulu:1649147	S2	Bengkulu	state	country:Indonesia:1643084	1	4561	2019-06-30 11:53:23.762993	\N	\N	100
state:Rize:740481	S2	Rize	state	country:Turkey:298795	1	1010	2019-07-02 17:29:27.530736	\N	\N	100
bay:BightOfBiafra:2309957	S2	Bight of Biafra	bay	root	1	4124	2019-07-01 18:59:22.479661	\N	\N	100
strait:MakassarStrait:1636782	S2	Makassar Strait	strait	root	1	17875	2019-06-30 13:47:01.328398	\N	\N	100
state:Karnataka:1267701	S2	Karnataka	state	region:South:8335041	1	16757	2019-07-01 17:20:03.166597	\N	\N	100
state:LaoCai:1562412	S2	Lào Cai	state	region:DongBac:11497248	1	1040	2019-07-01 13:12:56.973108	\N	\N	100
state:Kordestan:126584	S2	Kordestan	state	country:Iran:130758	1	7138	2019-06-30 19:21:36.596932	\N	\N	100
state:Teleorman:665283	S2	Teleorman	state	country:Romania:798549	1	2070	2019-06-30 18:40:16.560371	\N	\N	100
state:TadzhikistanTerritories:6452615	S2	Tadzhikistan Territories	state	region:KurganTyube:1220747	1	7085	2019-07-01 15:22:47.265939	\N	\N	100
state:Wellington:2179538	S2	Wellington	state	region:NorthIsland:2185983	1	1125	2019-07-01 07:39:56.664888	\N	\N	100
state:MadhyaPradesh:1264542	S2	Madhya Pradesh	state	region:Central:8335421	1	32397	2019-06-30 15:37:02.36242	\N	\N	100
state:Sabah:1733039	S2	Sabah	state	country:Malaysia:1733045	1	8027	2019-06-30 11:54:37.100455	\N	\N	100
state:Ardebil:413931	S2	Ardebil	state	country:Iran:130758	1	5354	2019-06-30 20:01:17.51966	\N	\N	100
state:MatabelelandNorth:886748	S2	Matabeleland North	state	country:Zimbabwe:878675	1	8761	2019-07-02 17:04:27.544763	\N	\N	100
state:MashonalandCentral:886843	S2	Mashonaland Central	state	country:Zimbabwe:878675	1	4604	2019-07-01 17:01:47.561912	\N	\N	100
state:BiokoSur:2566979	S2	Bioko Sur	state	country:EquatorialGuinea:2309096	1	507	2019-07-04 16:30:31.824141	\N	\N	100
strait:EstrechoDeMagellanes	S2	Estrecho de Magellanes	strait	root	1	5372	2019-06-30 10:14:52.597182	\N	\N	100
state:Babil:98227	S2	Babil	state	region:Iraq:99237	1	989	2019-07-01 16:19:48.420886	\N	\N	100
state:Vercelli:3164564	S2	Vercelli	state	region:Piemonte:3170831	1	1004	2019-07-02 01:43:38.340438	\N	\N	100
gulf:GulfOfThailand:1818185	S2	Gulf of Thailand	gulf	root	1	26914	2019-06-30 12:34:39.200705	\N	\N	100
region:KurganTyube:1220747	S2	Kurgan Tyube	region	country:Tajikistan:1220409	0	7085	2019-07-01 15:22:47.266405	\N	\N	100
state:Olt:671857	S2	Olt	state	country:Romania:798549	1	3089	2019-06-30 18:42:46.472711	\N	\N	100
state:Varese:3164697	S2	Varese	state	region:Lombardia:3174618	1	517	2019-07-04 19:01:28.763753	\N	\N	100
state:Samangan:1127766	S2	Samangan	state	country:Afghanistan:1149361	1	3484	2019-07-01 15:23:31.256995	\N	\N	100
state:ElBayadh:2498541	S2	El Bayadh	state	country:Algeria:2589581	1	9882	2019-06-30 09:51:52.878818	\N	\N	100
state:SaintLouis:2246451	S2	Saint-Louis	state	country:Senegal:2245662	1	3538	2019-07-02 20:11:28.784177	\N	\N	100
country:Mozambique:1036973	S2	Mozambique	country	continent:Africa:6255146	0	74943	2019-06-30 19:49:26.549806	\N	\N	100
state:Edirne:747711	S2	Edirne	state	country:Turkey:298795	1	2075	2019-06-30 19:10:26.440001	\N	\N	100
country:Belgium:2802361	S2	Belgium	country	continent:Europe:6255148	0	4892	2019-06-30 21:06:37.853358	\N	\N	100
region:Cascades:6930703	S2	Cascades	region	country:BurkinaFaso:2361809	0	2117	2019-06-30 09:51:46.515375	\N	\N	100
state:Shiga:1852553	S2	Shiga	state	region:Kinki:1859449	1	516	2019-06-30 10:33:39.377431	\N	\N	100
state:Canillo:3041203	S2	Canillo	state	country:Andorra:3041565	1	1026	2019-06-30 21:08:15.825525	\N	\N	100
state:Vladimir:826294	S2	Vladimir	state	region:Central:11961322	1	5962	2019-06-30 19:03:09.380781	\N	\N	100
state:Lobaye:2385105	S2	Lobaye	state	country:CentralAfricanRepublic:239880	1	2672	2019-06-30 18:44:24.40995	\N	\N	100
state:Savnik:3191221	S2	Šavnik	state	country:Montenegro:3194884	1	1017	2019-07-01 18:26:56.604141	\N	\N	100
state:Mafraq:250583	S2	Mafraq	state	country:Jordan:248816	1	5433	2019-06-30 19:06:49.915635	\N	\N	100
state:Louga:2249221	S2	Louga	state	country:Senegal:2245662	1	4052	2019-07-02 20:12:08.630431	\N	\N	100
region:SouthwestChina:	S2	Southwest China	region	country:China:1814991	0	237780	2019-06-30 10:14:46.419733	\N	\N	100
state:Rezina:617501	S2	Rezina	state	country:Moldova:617790	1	2081	2019-06-30 18:39:36.8505	\N	\N	100
state:Niederosterreich:2770542	S2	Niederösterreich	state	country:Austria:2782113	1	3705	2019-06-30 18:06:16.144185	\N	\N	100
state:Gifu:1863640	S2	Gifu	state	region:Chubu:1864496	1	514	2019-06-30 10:33:39.378219	\N	\N	100
state:Andrijevica:3343959	S2	Andrijevica	state	country:Montenegro:3194884	1	2032	2019-07-01 18:26:56.602233	\N	\N	100
state:Telenesti:617255	S2	Teleneşti	state	country:Moldova:617790	1	2087	2019-06-30 18:39:36.851147	\N	\N	100
country:Jordan:248816	S2	Jordan	country	continent:Asia:6255147	0	13208	2019-06-30 19:06:49.916585	\N	\N	100
state:Grigoriopol:618234	S2	Grigoriopol	state	country:Moldova:617790	1	1032	2019-06-30 18:39:36.84795	\N	\N	100
state:Antofagasta:3899537	S2	Antofagasta	state	country:Chile:3895114	1	14891	2019-07-01 01:37:13.709009	\N	\N	100
state:Masvingo:886761	S2	Masvingo	state	country:Zimbabwe:878675	1	8316	2019-07-01 17:04:53.127109	\N	\N	100
state:Mahakali:1283098	S2	Mahakali	state	region:FarWestern:7289705	1	1570	2019-07-01 15:58:07.178537	\N	\N	100
state:Ordino:3039676	S2	Ordino	state	country:Andorra:3041565	1	1026	2019-06-30 21:08:15.8261	\N	\N	100
state:Zagrebacka:3337531	S2	Zagrebacka	state	region:JugovzhodnaSlovenija:	1	2009	2019-06-30 18:01:16.447793	\N	\N	100
state:SantJuliaDeLoria:3039162	S2	Sant Julià de Lòria	state	country:Andorra:3041565	1	1025	2019-06-30 21:08:15.826723	\N	\N	100
country:Andorra:3041565	S2	Andorra	country	continent:Europe:6255148	0	1028	2019-06-30 21:08:15.827358	\N	\N	100
state:Danilovgrad:3202193	S2	Danilovgrad	state	country:Montenegro:3194884	1	1021	2019-07-01 18:26:56.605072	\N	\N	100
state:Midlands:886119	S2	Midlands	state	country:Zimbabwe:878675	1	6136	2019-07-01 16:59:48.900814	\N	\N	100
state:Braila:683901	S2	Braila	state	country:Romania:798549	1	2087	2019-06-30 18:47:28.788207	\N	\N	100
state:Amazonas:3689982	S2	Amazonas	state	country:Colombia:3686110	1	15398	2019-06-30 10:14:12.323743	\N	\N	100
state:AlBasrah:89341	S2	Al-Basrah	state	region:Iraq:99237	1	2002	2019-06-30 19:49:55.570955	\N	\N	100
state:VirovitickoPodravska:3337533	S2	Viroviticko-Podravska	state	country:Croatia:3202326	1	2066	2019-07-02 17:55:30.726167	\N	\N	100
state:Ninawa:92877	S2	Ninawa	state	region:Iraq:99237	1	5991	2019-07-01 16:13:00.80363	\N	\N	100
state:Podgorica:3189077	S2	Podgorica	state	country:Montenegro:3194884	1	2038	2019-07-01 18:26:56.60687	\N	\N	100
state:Vrancea:662447	S2	Vrancea	state	country:Romania:798549	1	1041	2019-06-30 18:42:42.451919	\N	\N	100
state:Zabljak:3186999	S2	Žabljak	state	country:Montenegro:3194884	1	1017	2019-07-01 18:26:56.602719	\N	\N	100
state:Amman:250439	S2	Amman	state	country:Jordan:248816	1	3572	2019-06-30 19:06:49.916113	\N	\N	100
state:Trnavsky:3343958	S2	Trnavský	state	country:Slovakia:3057568	1	1543	2019-06-30 18:18:54.312045	\N	\N	100
state:Mascara:2490095	S2	Mascara	state	country:Algeria:2589581	1	2070	2019-06-30 22:58:36.828341	\N	\N	100
state:Cuvette:2260487	S2	Cuvette	state	country:Congo:203312	1	629	2022-05-07 16:06:10.694246	\N	\N	100
state:Eastern:400741	S2	Eastern	state	country:Kenya:192950	1	16600	2019-06-30 20:12:41.872381	\N	\N	100
state:Lerida:6355231	S2	Lérida	state	region:Cataluna:3336901	1	1560	2019-06-30 21:06:25.366741	\N	\N	100
state:Mojkovac:3194925	S2	Mojkovac	state	country:Montenegro:3194884	1	1018	2019-07-01 18:26:56.603196	\N	\N	100
state:Tete:1026010	S2	Tete	state	country:Mozambique:1036973	1	12266	2019-06-30 20:03:23.347836	\N	\N	100
state:Orhei:617639	S2	Orhei	state	country:Moldova:617790	1	1046	2019-06-30 18:39:36.853074	\N	\N	100
country:Colombia:3686110	S2	Colombia	country	continent:SouthAmerica:6255150	0	105303	2019-06-30 10:10:40.944931	\N	\N	100
country:Kenya:192950	S2	Kenya	country	continent:Africa:6255146	0	51056	2019-06-30 20:12:41.873007	\N	\N	100
state:LaMassana:3040131	S2	La Massana	state	country:Andorra:3041565	1	1028	2019-06-30 21:08:15.824932	\N	\N	100
state:Podlachian:858789	S2	Podlachian	state	country:Poland:798544	1	4526	2019-07-01 18:58:25.691053	\N	\N	100
state:Khatlon:1347488	S2	Khatlon	state	region:Kulyab:1221194	1	2628	2019-07-01 15:24:45.826832	\N	\N	100
state:Taranaki:2181872	S2	Taranaki	state	region:NorthIsland:2185983	1	2000	2019-07-01 07:40:40.671952	\N	\N	100
region:Skopje:785841	S2	Skopje	region	country:Macedonia:718075	0	984	2019-07-01 19:03:49.208077	\N	\N	100
state:Tabuk:101627	S2	Tabuk	state	country:SaudiArabia:102358	1	13635	2019-06-30 19:07:02.178659	\N	\N	100
state:Tokat:738742	S2	Tokat	state	country:Turkey:298795	1	3104	2019-06-30 19:03:50.537702	\N	\N	100
region:JugovzhodnaSlovenija:	S2	Jugovzhodna Slovenija	region	country:Slovenia:3190538	0	1221	2022-05-05 15:02:16.416021	\N	\N	100
region:Eastern:9166195	S2	Eastern	region	country:Macedonia:718075	0	1993	2019-07-01 18:31:00.86273	\N	\N	100
state:Sliven:864557	S2	Sliven	state	country:Bulgaria:732800	1	516	2019-06-30 18:47:04.683818	\N	\N	100
region:Western:6470693	S2	Western	region	country:Thailand:1605651	0	5799	2019-06-30 10:14:46.670123	\N	\N	100
state:Burgas:732771	S2	Burgas	state	country:Bulgaria:732800	1	2863	2019-06-30 18:02:03.636936	\N	\N	100
region:Kulyab:1221194	S2	Kulyab	region	country:Tajikistan:1220409	0	2628	2019-07-01 15:24:45.827515	\N	\N	100
state:Ratchaburi:1150953	S2	Ratchaburi	state	region:Western:6470693	1	501	2019-07-01 14:54:35.120728	\N	\N	100
state:MatoGrosso:3457419	S2	Mato Grosso	state	country:Brazil:3469034	1	90396	2019-06-30 10:10:25.681679	\N	\N	100
state:Plav:3193227	S2	Plav	state	country:Montenegro:3194884	1	2041	2019-07-01 18:26:56.601237	\N	\N	100
state:RiftValley:400744	S2	Rift Valley	state	country:Kenya:192950	1	19728	2019-07-01 16:09:07.911598	\N	\N	100
state:Pljevlja:3193160	S2	Pljevlja	state	country:Montenegro:3194884	1	2031	2019-07-01 18:26:56.60596	\N	\N	100
state:StaroNagoricane:863899	S2	Staro Nagoričane	state	country:Macedonia:718075	1	982	2019-07-01 19:03:49.209415	\N	\N	100
region:West:1252881	S2	West	region	country:India:6269134	0	56350	2019-06-30 18:02:39.794589	\N	\N	100
state:Durango:4011741	S2	Durango	state	country:Mexico:3996063	1	16311	2019-07-01 05:34:44.647433	\N	\N	100
state:Antwerp:2803139	S2	Antwerp	state	region:Flemish:3337388	1	527	2019-07-02 19:18:13.287428	\N	\N	100
state:Targovishte:864560	S2	Targovishte	state	country:Bulgaria:732800	1	1027	2019-06-30 18:44:38.815904	\N	\N	100
state:VelikoTarnovo:864561	S2	Veliko Tarnovo	state	country:Bulgaria:732800	1	1946	2019-06-30 18:40:16.558092	\N	\N	100
state:GradZagreb:3337532	S2	Grad Zagreb	state	country:Croatia:3202326	1	972	2019-06-30 18:02:47.676381	\N	\N	100
state:Manicaland:887358	S2	Manicaland	state	country:Zimbabwe:878675	1	6212	2019-07-01 17:01:01.036912	\N	\N	100
state:AlButnan:7602688	S2	Al Butnan	state	country:Libya:2215636	1	10456	2019-06-30 18:44:21.722511	\N	\N	100
state:Asir:108179	S2	`Asir	state	country:SaudiArabia:102358	1	11295	2019-06-30 19:21:01.717054	\N	\N	100
state:RifDimashq:170652	S2	Rif Dimashq	state	country:Syria:163843	1	3091	2019-06-30 19:03:13.676899	\N	\N	100
state:Kolasin:3197896	S2	Kolašin	state	country:Montenegro:3194884	1	1020	2019-07-01 18:26:56.606412	\N	\N	100
state:MashonalandEast:886842	S2	Mashonaland East	state	country:Zimbabwe:878675	1	4705	2019-07-01 17:08:11.898477	\N	\N	100
sea:BoHai:1818205	S2	Bo Hai	sea	root	1	13387	2019-07-01 00:47:16.046175	\N	\N	100
state:Hentiy:2030783	S2	Hentiy	state	country:Mongolia:2029969	1	14885	2019-06-30 23:22:08.936716	\N	\N	100
state:KrapinskoZagorska:3337519	S2	Krapinsko-Zagorska	state	country:Croatia:3202326	1	1748	2019-06-30 18:02:47.675982	\N	\N	100
state:Homs(Hims):169575	S2	Homs (Hims)	state	country:Syria:163843	1	6612	2019-06-30 19:10:37.276448	\N	\N	100
state:Berane:3199071	S2	Berane	state	country:Montenegro:3194884	1	2032	2019-07-01 18:26:56.601769	\N	\N	100
state:BijeloPolje:3204173	S2	Bijelo Polje	state	country:Montenegro:3194884	1	2030	2019-07-01 18:26:56.60551	\N	\N	100
state:Tula:480508	S2	Tula	state	region:Central:11961322	1	4652	2019-07-01 19:45:35.479604	\N	\N	100
state:Littoral:2229336	S2	Littoral	state	country:Cameroon:2233387	1	2266	2019-07-01 18:51:40.557813	\N	\N	100
state:Kayseri:308463	S2	Kayseri	state	country:Turkey:298795	1	5196	2019-06-30 19:08:35.058587	\N	\N	100
state:Stip:863900	S2	Štip	state	region:Eastern:9166195	1	981	2019-07-01 19:03:49.208523	\N	\N	100
sea:WhiteSea:577742	S2	White Sea	sea	root	1	26633	2019-06-30 10:10:20.165117	\N	\N	100
state:Ariege:3036965	S2	Ariège	state	region:MidiPyrenees:11071623	1	2043	2019-06-30 21:08:15.829181	\N	\N	100
state:Yozgat:296560	S2	Yozgat	state	country:Turkey:298795	1	4174	2019-06-30 19:03:50.536726	\N	\N	100
sea:IrishSea:6640368	S2	Irish Sea	sea	root	1	14781	2019-06-30 18:34:03.680961	\N	\N	100
state:Bauchi:2347468	S2	Bauchi	state	country:Nigeria:2328926	1	5738	2019-07-01 18:47:57.024221	\N	\N	100
region:DongBac:11497248	S2	Đông Bắc	region	country:Vietnam:1562822	0	7903	2019-06-30 12:42:20.140704	\N	\N	100
state:Kaabong:233093	S2	Kaabong	state	region:Northern:8260674	1	1505	2019-07-04 14:32:12.198956	\N	\N	100
country:Qatar:289688	S2	Qatar	country	continent:Asia:6255147	0	1230	2019-07-04 14:49:41.871044	\N	\N	100
region:ProvenceAlpesCoteDAzur:2985244	S2	Provence-Alpes-Côte-d'Azur	region	country:France:3017382	0	4726	2019-06-30 09:51:56.692066	\N	\N	100
country:Montenegro:3194884	S2	Montenegro	country	continent:Europe:6255148	0	1558	2019-07-01 18:26:56.607321	\N	\N	100
region:Peloponnisos:6697807	S2	Peloponnisos	region	country:Greece:390903	0	5142	2019-06-30 18:43:40.987317	\N	\N	100
state:StereaEllada:8200475	S2	Stereá Elláda	state	country:Greece:390903	1	4112	2019-06-30 19:01:53.44143	\N	\N	100
state:Attiki:264353	S2	Attiki	state	region:Peloponnisos:6697807	1	1538	2019-06-30 18:59:50.497945	\N	\N	100
state:Balkan:162152	S2	Balkan	state	country:Turkmenistan:1218197	1	21387	2019-07-01 18:22:17.47886	\N	\N	100
country:Bulgaria:732800	S2	Bulgaria	country	continent:Europe:6255148	0	16315	2019-06-30 18:02:03.637609	\N	\N	100
state:Cahul:618164	S2	Cahul	state	country:Moldova:617790	1	3095	2019-06-30 19:01:15.852971	\N	\N	100
state:Galati:677692	S2	Galati	state	country:Romania:798549	1	1558	2019-06-30 19:01:15.85435	\N	\N	100
region:Southern:10234924	S2	Southern	region	country:Thailand:1605651	0	7736	2019-07-01 14:39:38.88794	\N	\N	100
state:Qinghai:1280239	S2	Qinghai	state	region:NorthwestChina:	1	79617	2019-06-30 13:32:32.522953	\N	\N	100
state:Kumanovo:863873	S2	Kumanovo	state	region:Northeastern:9072895	1	979	2019-07-01 19:03:49.209871	\N	\N	100
state:SouthKordufan:408661	S2	South Kordufan	state	region:Kordofan:408667	1	15247	2019-06-30 16:05:59.045016	\N	\N	100
country:Spain:2510769	S2	Spain	country	continent:Europe:6255148	0	71430	2019-06-30 20:54:54.148674	\N	\N	100
state:Smolensk:491684	S2	Smolensk	state	region:Central:11961322	1	9452	2019-06-30 17:30:41.70339	\N	\N	100
state:Bacau:685947	S2	Bacau	state	country:Romania:798549	1	1541	2019-06-30 19:01:31.714715	\N	\N	100
state:Vaslui:663116	S2	Vaslui	state	country:Romania:798549	1	1033	2019-06-30 19:03:24.823506	\N	\N	100
state:Rondonia:3924825	S2	Rondônia	state	country:Brazil:3469034	1	22659	2019-06-30 23:20:11.770718	\N	\N	100
region:Northeastern:9072895	S2	Northeastern	region	country:Macedonia:718075	0	1995	2019-07-01 18:31:00.864136	\N	\N	100
continent:Asia:6255147	S2	Asia	continent	root	0	3908502	2019-06-30 10:10:20.742384	\N	\N	100
state:MinasGerais:3457153	S2	Minas Gerais	state	country:Brazil:3469034	1	55441	2019-06-30 09:52:39.857692	\N	\N	100
state:Chumphon:1153555	S2	Chumphon	state	region:Southern:10234924	1	2573	2019-07-01 14:51:38.649926	\N	\N	100
state:Bekes:722433	S2	Békés	state	region:GreatSouthernPlain:	1	3556	2019-07-01 18:38:46.088275	\N	\N	100
state:Dzavhan:1516012	S2	Dzavhan	state	country:Mongolia:2029969	1	15033	2019-07-01 14:08:23.805626	\N	\N	100
state:Cantemir:618142	S2	Cantemir	state	country:Moldova:617790	1	2057	2019-06-30 19:03:24.822262	\N	\N	100
state:Leova:617903	S2	Leova	state	country:Moldova:617790	1	2057	2019-06-30 19:03:24.8214	\N	\N	100
state:Hincesti:858803	S2	Hîncesti	state	country:Moldova:617790	1	4125	2019-06-30 18:39:36.846628	\N	\N	100
state:Lacs:2597324	S2	Lacs	state	country:IvoryCoast:2287781	1	2085	2019-06-30 09:51:43.8872	\N	\N	100
state:Arad:686253	S2	Arad	state	country:Romania:798549	1	1026	2019-07-01 18:38:46.089237	\N	\N	100
state:Shizuoka:1851715	S2	Shizuoka	state	region:Chubu:1864496	1	2051	2019-06-30 10:14:20.389906	\N	\N	100
state:Erzincan:315372	S2	Erzincan	state	country:Turkey:298795	1	3399	2019-06-30 19:03:17.747337	\N	\N	100
state:NotioAigaio:6697808	S2	Notio Aigaio	state	country:Greece:390903	1	1220	2019-06-30 19:11:54.643121	\N	\N	100
state:Xacmaz:586001	S2	Xaçmaz	state	region:GubaKhachmazEconomicRegion:	1	1483	2019-07-03 16:28:33.107723	\N	\N	100
state:Morobe:2090468	S2	Morobe	state	country:PapuaNewGuinea:2088628	1	3491	2019-07-01 09:40:30.783412	\N	\N	100
state:Gumushane:746423	S2	Gümüshane	state	country:Turkey:298795	1	442	2019-06-30 19:09:29.32404	\N	\N	100
state:Drugovo:863851	S2	Drugovo	state	country:Macedonia:718075	1	1005	2019-07-01 18:37:40.409781	\N	\N	100
state:Makira:2178730	S2	Makira	state	country:SolomonIslands:2103350	1	994	2019-07-03 05:02:01.693123	\N	\N	100
state:Diber:865731	S2	Dibër	state	country:Albania:783754	1	1009	2019-07-01 18:37:40.414495	\N	\N	100
state:Berat:865730	S2	Berat	state	country:Albania:783754	1	1509	2019-07-01 18:55:17.052686	\N	\N	100
state:Durres:3344951	S2	Durrës	state	country:Albania:783754	1	1009	2019-07-01 18:55:17.053569	\N	\N	100
state:Plasnica:863887	S2	Plasnica	state	country:Macedonia:718075	1	1951	2019-07-01 18:59:45.972062	\N	\N	100
state:Resen:786339	S2	Resen	state	region:Southwestern:9072896	1	1955	2019-07-01 18:59:45.97303	\N	\N	100
region:Southwestern:9072896	S2	Southwestern	region	country:Macedonia:718075	0	1959	2019-07-01 18:59:45.973557	\N	\N	100
state:Korce:865734	S2	Korçë	state	country:Albania:783754	1	1519	2019-07-01 18:59:45.976346	\N	\N	100
country:Albania:783754	S2	Albania	country	continent:Europe:6255148	0	4612	2019-07-01 18:30:28.945497	\N	\N	100
state:KasaiOriental:214138	S2	Kasaï-Oriental	state	country:Congo:203312	1	17116	2019-06-30 15:49:20.529192	\N	\N	100
state:Elbasan:865732	S2	Elbasan	state	country:Albania:783754	1	505	2019-07-01 18:59:45.976988	\N	\N	100
state:AdDawhah:389470	S2	Ad Dawhah	state	country:Qatar:289688	1	697	2019-07-04 14:49:41.869266	\N	\N	100
state:AlDhahira:411739	S2	Al Dhahira	state	country:Oman:286963	1	3591	2019-06-30 15:47:27.715296	\N	\N	100
state:Yapanaya:1242831	S2	Yāpanaya	state	region:UturuPalata:	1	515	2019-06-30 15:42:28.040472	\N	\N	100
sea:LaccadiveSea:1237875	S2	Laccadive Sea	sea	root	1	63918	2019-06-30 15:20:41.350299	\N	\N	100
gulf:GulfOfAden:80303	S2	Gulf of Aden	gulf	root	1	26181	2019-06-30 20:33:43.396239	\N	\N	100
state:Virginia:6254928	S2	Virginia	state	region:South:11887752	1	12509	2019-07-01 02:12:11.377667	\N	\N	100
state:AlWakrah:389472	S2	Al Wakrah	state	country:Qatar:289688	1	526	2019-07-04 14:49:41.869889	\N	\N	100
state:Tashauz:601465	S2	Tashauz	state	country:Turkmenistan:1218197	1	13224	2019-07-01 18:25:55.202832	\N	\N	100
state:AshSharqiyah:108241	S2	Ash Sharqiyah	state	country:SaudiArabia:102358	1	67614	2019-06-30 19:19:19.870206	\N	\N	100
country:Turkmenistan:1218197	S2	Turkmenistan	country	continent:Asia:6255147	0	69175	2019-06-30 14:43:46.630081	\N	\N	100
strait:PalkStrait:1231851	S2	Palk Strait	strait	root	1	1567	2019-06-30 15:22:56.549793	\N	\N	100
state:Yunnan:1785694	S2	Yunnan	state	region:SouthwestChina:	1	38769	2019-06-30 10:14:46.419098	\N	\N	100
state:Khorezm:1484843	S2	Khorezm	state	country:Uzbekistan:1512440	1	2999	2019-06-30 15:01:56.460938	\N	\N	100
state:Amhara:444180	S2	Amhara	state	country:Ethiopia:337996	1	16849	2019-07-01 16:10:47.30387	\N	\N	100
state:Luapula:909845	S2	Luapula	state	country:Zambia:895949	1	7226	2019-07-02 15:33:49.236023	\N	\N	100
state:Altay:1511732	S2	Altay	state	region:Siberian:11961345	1	31007	2019-06-30 16:39:00.568669	\N	\N	100
country:Oman:286963	S2	Oman	country	continent:Asia:6255147	0	34010	2019-06-30 15:46:26.388222	\N	\N	100
state:KievCity:10291296	S2	Kiev City	state	country:Ukraine:690791	1	4129	2019-06-30 18:44:42.226215	\N	\N	100
region:CagayanValley(RegionIi):	S2	Cagayan Valley (Region II)	region	country:Philippines:1694008	0	2501	2019-07-02 10:09:45.669017	\N	\N	100
region:NorthIsland:2185983	S2	North Island	region	country:NewZealand:2186224	0	19970	2019-07-01 07:40:40.672805	\N	\N	100
sea:DavisSea:4036627	S2	Davis Sea	sea	root	1	14830	2019-08-25 09:51:19.741149	\N	\N	100
state:Kiev:703447	S2	Kiev	state	country:Ukraine:690791	1	8819	2019-06-30 17:39:02.499104	\N	\N	100
state:SuratThani:1150514	S2	Surat Thani	state	region:Southern:10234924	1	1711	2019-07-01 14:55:26.404755	\N	\N	100
state:Cagayan:1721086	S2	Cagayan	state	region:CagayanValley(RegionIi):	1	1026	2019-07-02 10:11:28.676916	\N	\N	100
state:AlMuthannia:99032	S2	Al-Muthannia	state	region:Iraq:99237	1	5418	2019-06-30 19:23:23.347055	\N	\N	100
state:Udmurt:479613	S2	Udmurt	state	region:Volga:11961325	1	6549	2019-07-01 15:40:53.242452	\N	\N	100
state:WestKazakhstan:607847	S2	West Kazakhstan	state	country:Kazakhstan:1522867	1	26004	2019-06-30 19:50:00.082163	\N	\N	100
state:Oudomxai:1654491	S2	Oudômxai	state	country:Laos:1655842	1	2057	2019-07-01 13:35:42.153609	\N	\N	100
state:AlMarj:88903	S2	Al Marj	state	country:Libya:2215636	1	3086	2019-06-30 18:00:18.789704	\N	\N	100
state:Batman:443186	S2	Batman	state	country:Turkey:298795	1	1987	2019-07-04 13:12:53.886414	\N	\N	100
state:Tekirdag:738926	S2	Tekirdag	state	country:Turkey:298795	1	2067	2019-06-30 19:05:05.257022	\N	\N	100
day:30	S2	30	day	root	1	889858	2019-06-30 10:31:47.497827	\N	\N	100
river:AmazonRiver:3407729	S2	Amazon River	river	root	1	1545	2019-07-03 20:01:15.180735	\N	\N	100
state:Bingol:321079	S2	Bingöl	state	country:Turkey:298795	1	2005	2019-07-02 17:04:21.367932	\N	\N	100
state:Diyarbakir:316540	S2	Diyarbakir	state	country:Turkey:298795	1	2644	2019-07-02 16:32:03.689981	\N	\N	100
state:AlJabalAlAkhdar:443289	S2	Al Jabal al Akhdar	state	country:Libya:2215636	1	2392	2019-06-30 18:44:19.45846	\N	\N	100
state:Novosibirsk:1496745	S2	Novosibirsk	state	region:Siberian:11961345	1	26509	2019-06-30 16:36:37.490734	\N	\N	100
state:Kirklareli:743165	S2	Kirklareli	state	country:Turkey:298795	1	2078	2019-06-30 18:02:03.634983	\N	\N	100
state:CaboDelgado:1051823	S2	Cabo Delgado	state	country:Mozambique:1036973	1	6539	2019-06-30 20:12:48.421601	\N	\N	100
state:DonetsK:9644144	S2	Donets'k	state	country:Ukraine:690791	1	6695	2019-06-30 19:03:47.741798	\N	\N	100
channel:CanalDoNorte:7622764	S2	Canal do Norte	channel	root	1	1016	2019-07-03 20:04:07.125749	\N	\N	100
state:LopBuri:1609031	S2	Lop Buri	state	region:Central:10177180	1	1009	2019-07-01 14:21:19.165206	\N	\N	100
state:Mus:304041	S2	Mus	state	country:Turkey:298795	1	2007	2019-07-02 17:03:23.789223	\N	\N	100
state:Ghazni:1141268	S2	Ghazni	state	country:Afghanistan:1149361	1	2965	2019-07-01 15:26:11.661771	\N	\N	100
state:Almaty:1537162	S2	Almaty	state	country:Kazakhstan:1522867	1	32059	2019-06-30 16:36:34.075097	\N	\N	100
state:AlHududAshShamaliyah:109579	S2	Al Hudud ash Shamaliyah	state	country:SaudiArabia:102358	1	17929	2019-06-30 19:23:23.345811	\N	\N	100
state:Mbomou:237556	S2	Mbomou	state	country:CentralAfricanRepublic:239880	1	6180	2019-07-01 23:02:11.556929	\N	\N	100
state:Bitlis:321022	S2	Bitlis	state	country:Turkey:298795	1	1000	2019-07-04 13:23:51.527399	\N	\N	100
state:Northern:2297169	S2	Northern	state	country:Ghana:2300660	1	8445	2019-06-30 09:51:47.867091	\N	\N	100
state:NordurlandVestra:3337403	S2	Norðurland vestra	state	country:Iceland:2629691	1	2818	2019-06-30 21:36:41.190619	\N	\N	100
state:AlBahrAlAhmar:361468	S2	Al Bahr al Ahmar	state	country:Egypt:357994	1	20740	2019-06-30 19:05:38.089213	\N	\N	100
state:Oubritenga:2356983	S2	Oubritenga	state	region:PlateauCentral:6930712	1	2546	2019-06-30 09:51:49.261412	\N	\N	100
state:Bukhoro:1217662	S2	Bukhoro	state	country:Uzbekistan:1512440	1	7234	2019-06-30 14:42:31.832363	\N	\N	100
state:RedSea:408646	S2	Red Sea	state	region:Kassala:408663	1	24638	2019-06-30 19:12:03.829336	\N	\N	100
state:Amasya:752014	S2	Amasya	state	country:Turkey:298795	1	3588	2019-06-30 19:12:54.572574	\N	\N	100
state:CampbellIslands:2192678	S2	Campbell Islands	state	region:NewZealandOutlyingIslands:	1	1940	2019-07-01 08:30:39.69171	\N	\N	100
region:Kassala:408663	S2	Kassala	region	country:Sudan:366755	0	36588	2019-06-30 19:12:03.829967	\N	\N	100
country:Syria:163843	S2	Syria	country	continent:Asia:6255147	0	23249	2019-06-30 19:01:27.441284	\N	\N	100
state:Samsun:740263	S2	Samsun	state	country:Turkey:298795	1	2569	2019-06-30 19:12:54.573138	\N	\N	100
bay:PrydzBay:6623681	S2	Prydz Bay	bay	root	1	5052	2019-08-30 10:25:22.366056	\N	\N	100
state:SzabolcsSzatmarBereg:715593	S2	Szabolcs-Szatmár-Bereg	state	region:NorthernGreatPlain:	1	1493	2019-07-01 18:56:09.256585	\N	\N	100
region:PlateauCentral:6930712	S2	Plateau-Central	region	country:BurkinaFaso:2361809	0	3050	2019-06-30 09:51:49.262215	\N	\N	100
state:Hasaka(AlHaksa):173813	S2	Hasaka (Al Haksa)	state	country:Syria:163843	1	3565	2019-07-02 17:03:04.024347	\N	\N	100
country:BurkinaFaso:2361809	S2	Burkina Faso	country	continent:Africa:6255146	0	28118	2019-06-30 09:51:46.515975	\N	\N	100
state:Bucharest:683504	S2	Bucharest	state	country:Romania:798549	1	514	2019-06-30 19:02:44.037006	\N	\N	100
state:Tomsk:1489421	S2	Tomsk	state	region:Siberian:11961345	1	55553	2019-06-30 16:36:32.250698	\N	\N	100
state:Kandahar:1138335	S2	Kandahar	state	country:Afghanistan:1149361	1	7665	2019-07-01 15:03:17.950397	\N	\N	100
state:Giurgiu:677104	S2	Giurgiu	state	country:Romania:798549	1	3085	2019-06-30 18:40:16.559901	\N	\N	100
state:EasternDarfur:8394435	S2	Eastern Darfur	state	region:Darfur:408660	1	5545	2019-07-01 21:29:42.833458	\N	\N	100
state:Ilfov:865518	S2	Ilfov	state	country:Romania:798549	1	514	2019-06-30 19:02:44.038357	\N	\N	100
state:Prahova:669737	S2	Prahova	state	country:Romania:798549	1	3088	2019-06-30 18:09:25.09849	\N	\N	100
state:Orientale:216139	S2	Orientale	state	country:Congo:203312	1	48648	2019-06-30 16:07:13.372129	\N	\N	100
state:Farah:1142263	S2	Farah	state	country:Afghanistan:1149361	1	7083	2019-06-30 14:45:03.922402	\N	\N	100
state:Wasit:89693	S2	Wasit	state	region:Iraq:99237	1	810	2019-07-03 15:51:38.716445	\N	\N	100
state:DayrAzZawr:170792	S2	Dayr Az Zawr	state	country:Syria:163843	1	4292	2019-07-02 16:14:35.258749	\N	\N	100
state:Calarasi:683016	S2	Calarasi	state	country:Romania:798549	1	2011	2019-06-30 18:47:28.787759	\N	\N	100
state:Buzau:683121	S2	Buzau	state	country:Romania:798549	1	1033	2019-06-30 18:42:42.452388	\N	\N	100
state:Ilam:130801	S2	Ilam	state	country:Iran:130758	1	2988	2019-06-30 19:21:43.35269	\N	\N	100
state:Ialomita:675848	S2	Ialomita	state	country:Romania:798549	1	1546	2019-06-30 18:47:28.788643	\N	\N	100
state:Pskov:504338	S2	Pskov	state	region:Northwestern:11961321	1	9838	2019-06-30 10:10:23.462139	\N	\N	100
state:Saraburi:1606417	S2	Saraburi	state	region:Central:10177180	1	498	2019-07-01 14:43:38.730608	\N	\N	100
country:Guyana:3378535	S2	Guyana	country	continent:SouthAmerica:6255150	0	21738	2019-06-30 23:41:09.666483	\N	\N	100
sea:SeaOfCrete:258761	S2	Sea of Crete	sea	root	1	12453	2019-06-30 18:43:40.985438	\N	\N	100
state:Krasnodar:542415	S2	Krasnodar	state	region:Volga:11961325	1	12183	2019-06-30 19:03:25.032009	\N	\N	100
region:LankaranEconomicRegion:	S2	Lankaran Economic Region	region	country:Azerbaijan:587116	0	4495	2019-06-30 19:49:02.700245	\N	\N	100
region:Eastern:7114724	S2	Eastern	region	country:Thailand:1605651	0	5553	2019-07-01 14:27:33.766427	\N	\N	100
state:PrachinBuri:1906687	S2	Prachin Buri	state	region:Eastern:7114724	1	21	2019-07-21 10:06:50.619094	\N	\N	100
state:Tambov:484638	S2	Tambov	state	region:Central:11961322	1	10306	2019-06-30 19:04:33.291199	\N	\N	100
state:Fuzuli:148107	S2	Füzuli	state	region:YukhariGarabakhEconomicRegion:	1	493	2019-07-03 16:41:27.29172	\N	\N	100
state:Chelyabinsk:1508290	S2	Chelyabinsk	state	region:Urals:466003	1	17557	2019-06-30 19:13:44.68464	\N	\N	100
state:Biləsuvar:147310	S2	Biləsuvar	state	region:AranEconomicRegion:	1	1987	2019-06-30 19:49:02.69883	\N	\N	100
state:Cəlilabad:148155	S2	Cəlilabad	state	region:LankaranEconomicRegion:	1	1988	2019-06-30 19:49:02.699717	\N	\N	100
state:Kriti:6697802	S2	Kriti	state	country:Greece:390903	1	3605	2019-06-30 18:43:54.122131	\N	\N	100
state:BarimaWaini:3379515	S2	Barima-Waini	state	country:Guyana:3378535	1	3058	2019-06-30 23:41:09.665991	\N	\N	100
state:Kilis:443213	S2	Kilis	state	country:Turkey:298795	1	507	2019-06-30 19:19:40.728531	\N	\N	100
state:CuyuniMazaruni:3376407	S2	Cuyuni-Mazaruni	state	country:Guyana:3378535	1	2004	2019-06-30 23:43:32.115667	\N	\N	100
state:Dobrich:726419	S2	Dobrich	state	country:Bulgaria:732800	1	1546	2019-06-30 19:04:55.282314	\N	\N	100
state:Ardahan:862470	S2	Ardahan	state	country:Turkey:298795	1	1499	2019-07-04 13:24:13.368644	\N	\N	100
state:Ajdabiya:7602692	S2	Ajdabiya	state	country:Libya:2215636	1	20217	2019-06-30 18:09:04.345031	\N	\N	100
state:Phongsali:1653893	S2	Phôngsali	state	country:Laos:1655842	1	3573	2019-07-01 13:52:26.848822	\N	\N	100
state:Aleppo:170062	S2	Aleppo	state	country:Syria:163843	1	3576	2019-06-30 19:01:27.440779	\N	\N	100
state:Shumen:864555	S2	Shumen	state	country:Bulgaria:732800	1	3876	2019-06-30 18:41:29.765439	\N	\N	100
state:Gaziantep:314829	S2	Gaziantep	state	country:Turkey:298795	1	2047	2019-06-30 19:11:46.609641	\N	\N	100
state:Varna:726051	S2	Varna	state	country:Bulgaria:732800	1	1821	2019-06-30 18:41:29.765974	\N	\N	100
state:Artvin:751816	S2	Artvin	state	country:Turkey:298795	1	3031	2019-07-02 17:04:21.634932	\N	\N	100
state:BuengKan:8133594	S2	Bueng Kan	state	region:Northeastern:	1	583	2019-07-03 11:15:56.479191	\N	\N	100
state:Constanta:680962	S2	Constanta	state	country:Romania:798549	1	4116	2019-06-30 18:47:28.786933	\N	\N	100
state:LaiChau:1577954	S2	Lai Chau	state	region:DongBac:11497248	1	505	2019-07-01 14:04:59.030801	\N	\N	100
state:Silistra:864556	S2	Silistra	state	country:Bulgaria:732800	1	2062	2019-06-30 18:44:38.816705	\N	\N	100
state:DienBien:1905100	S2	Điện Biên	state	region:TayBac:11497246	1	1520	2019-07-01 14:04:59.031445	\N	\N	100
region:KalbajarLachinEconomicRegion:	S2	Kalbajar-Lachin Economic Region	region	country:Azerbaijan:587116	0	2968	2019-07-01 16:19:49.648895	\N	\N	100
state:AlQubbah:87204	S2	Al Qubbah	state	country:Libya:2215636	1	1567	2019-06-30 18:48:00.755971	\N	\N	100
state:Daugavpils:460413	S2	Daugavpils	state	region:Latgale:7639662	1	1511	2019-07-01 19:00:11.9749	\N	\N	100
state:Chernihiv:710734	S2	Chernihiv	state	country:Ukraine:690791	1	6810	2019-06-30 17:32:47.503168	\N	\N	100
state:Lankaran:147622	S2	Lankaran	state	region:KalbajarLachinEconomicRegion:	1	606	2022-05-06 14:19:35.08602	\N	\N	100
region:Latgale:7639662	S2	Latgale	region	country:Latvia:458258	0	3951	2019-06-30 10:10:32.188909	\N	\N	100
state:SuphanBuri:1606032	S2	Suphan Buri	state	region:Central:10177180	1	1524	2019-07-01 14:45:51.807129	\N	\N	100
region:YukhariGarabakhEconomicRegion:	S2	Yukhari Garabakh Economic Region	region	country:Azerbaijan:587116	0	1482	2019-07-01 16:19:49.646481	\N	\N	100
state:Jekabpils:459282	S2	Jekabpils	state	region:Zemgale:7639660	1	1008	2019-07-01 19:01:43.374556	\N	\N	100
state:Madona:457712	S2	Madona	state	region:Vidzeme:7639661	1	1016	2019-06-30 10:10:32.189502	\N	\N	100
state:Syunik:409314	S2	Syunik	state	country:Armenia:174982	1	1985	2019-07-01 16:29:30.859199	\N	\N	100
state:Xocavənd:146969	S2	Xocavənd	state	region:YukhariGarabakhEconomicRegion:	1	492	2019-07-03 16:09:36.241824	\N	\N	100
state:Aomori:2130656	S2	Aomori	state	region:Tohoku:2110769	1	1520	2019-06-30 10:14:38.652916	\N	\N	100
strait:CookStrait:2194167	S2	Cook Strait	strait	root	1	4491	2019-07-01 07:40:33.429783	\N	\N	100
region:Central:7289707	S2	Central	region	country:Nepal:1282988	0	4172	2019-06-30 15:35:55.749104	\N	\N	100
state:Ferghana:1514019	S2	Ferghana	state	country:Uzbekistan:1512440	1	3390	2019-07-01 15:15:22.83529	\N	\N	100
state:Guera:2431555	S2	Guéra	state	country:Chad:2434508	1	9224	2019-06-30 16:55:01.074345	\N	\N	100
state:Chuuk:2082282	S2	Chuuk	state	country:Micronesia:2081918	1	134	2022-06-07 03:11:49.981226	\N	\N	100
region:Tohoku:2110769	S2	Tohoku	region	country:Japan:1861060	0	8284	2019-06-30 10:13:56.780223	\N	\N	100
state:Satun:1606375	S2	Satun	state	region:Southern:10234924	1	494	2019-07-01 14:29:16.060529	\N	\N	100
state:Kraslavas:458621	S2	Kraslavas	state	region:Latgale:7639662	1	1010	2019-07-01 18:51:41.331947	\N	\N	100
state:Perlis:1733040	S2	Perlis	state	country:Malaysia:1733045	1	987	2019-07-01 14:29:16.06	\N	\N	100
state:Batken:1463580	S2	Batken	state	country:Kyrgyzstan:1527747	1	4131	2019-07-01 15:15:22.83627	\N	\N	100
state:Narayani:1283000	S2	Narayani	state	region:Central:7289707	1	1044	2019-06-30 15:35:55.74863	\N	\N	100
state:Sigave:4034776	S2	Sigave	state	country:WallisAndFutuna:4034749	1	499	2019-07-01 07:44:12.656942	\N	\N	100
state:GornoBadakhshan:1221692	S2	Gorno-Badakhshan	state	region:Dushanbe:7280679	1	9654	2019-06-30 16:17:15.273118	\N	\N	100
state:Dumyat:358044	S2	Dumyat	state	country:Egypt:357994	1	517	2019-07-03 15:25:39.739568	\N	\N	100
state:Florida:4155751	S2	Florida	state	region:South:11887752	1	15387	2019-06-30 10:16:21.171874	\N	\N	100
state:Alo:4034884	S2	Alo	state	country:WallisAndFutuna:4034749	1	500	2019-07-01 07:44:12.657691	\N	\N	100
state:Jiangsu:1806260	S2	Jiangsu	state	region:EastChina:6493581	1	12346	2019-06-30 11:12:09.260591	\N	\N	100
state:AdDakhliyah:411735	S2	Ad Dakhliyah	state	country:Oman:286963	1	3133	2019-06-30 15:47:27.715756	\N	\N	100
region:Dushanbe:7280679	S2	Dushanbe	region	country:Tajikistan:1220409	0	9802	2019-06-30 16:17:15.27358	\N	\N	100
country:Tajikistan:1220409	S2	Tajikistan	country	continent:Asia:6255147	0	19254	2019-06-30 16:17:15.274056	\N	\N	100
state:AdDaqahliyah:361849	S2	Ad Daqahliyah	state	country:Egypt:357994	1	1038	2019-07-01 21:37:54.684477	\N	\N	100
state:AlGharbiyah:361294	S2	Al Gharbiyah	state	country:Egypt:357994	1	2033	2019-07-01 21:37:42.846325	\N	\N	100
state:KafrAshShaykh:354500	S2	Kafr ash Shaykh	state	country:Egypt:357994	1	2096	2019-07-01 21:37:42.846736	\N	\N	100
state:AshSharqiyah:360016	S2	Ash Sharqiyah	state	country:Egypt:357994	1	1037	2019-07-03 15:25:39.74113	\N	\N	100
state:NewYork:5128638	S2	New York	state	region:Northeast:11887749	1	18132	2019-07-01 01:45:19.267985	\N	\N	100
day:05	S2	05	day	root	1	936392	2019-07-05 04:36:58.024659	\N	\N	100
state:BaniSuwayf:359171	S2	Bani Suwayf	state	country:Egypt:357994	1	3074	2019-07-01 21:39:24.895357	\N	\N	100
state:Uvea:4034749	S2	`Uvea	state	country:WallisAndFutuna:4034749	1	498	2019-07-01 07:42:03.954904	\N	\N	100
state:AlFayyum:361323	S2	Al Fayyum	state	country:Egypt:357994	1	1553	2019-07-01 21:39:54.560606	\N	\N	100
country:WallisAndFutuna:4034749	S2	Wallis and Futuna	country	continent:Oceania:6255151	0	997	2019-07-01 07:42:03.955446	\N	\N	100
ocean:SouthernOcean:4036776	S2	Southern Ocean	ocean	root	1	210726	2019-08-25 02:35:58.352163	\N	\N	100
state:Roraima:3662560	S2	Roraima	state	country:Brazil:3469034	1	24297	2019-06-30 23:22:29.518409	\N	\N	100
country:Congo:203312	S2	Congo	country	continent:Africa:6255146	0	207502	2019-06-30 15:39:20.052484	\N	\N	100
country:WesternSahara:2461445	S2	Western Sahara	country	continent:Africa:6255146	0	11761	2019-07-01 20:55:17.055351	\N	\N	100
state:Plateau:2324828	S2	Plateau	state	country:Nigeria:2328926	1	2204	2019-07-04 15:34:36.652578	\N	\N	100
state:Louangphrabang:1655558	S2	Louangphrabang	state	country:Laos:1655842	1	2537	2019-07-01 13:42:37.931437	\N	\N	100
state:Kaduna:2335722	S2	Kaduna	state	country:Nigeria:2328926	1	6205	2019-07-02 18:30:03.847757	\N	\N	100
state:Norrbotten:604010	S2	Norrbotten	state	country:Sweden:2661886	1	25705	2019-06-30 09:52:07.097978	\N	\N	100
state:Jura:3012051	S2	Jura	state	region:FrancheComte:11071619	1	3074	2019-07-02 19:15:57.185531	\N	\N	100
region:FrancheComte:11071619	S2	Franche-Comté	region	country:France:3017382	0	4715	2019-07-02 19:04:31.619481	\N	\N	100
region:Central:10177180	S2	Central	region	country:Thailand:1605651	0	9487	2019-07-01 14:20:55.661108	\N	\N	100
state:AlQuassim:	S2	Al Quassim	state	country:SaudiArabia:102358	1	8038	2019-07-01 16:08:21.980268	\N	\N	100
gulf:GulfOfOb:1496399	S2	Gulf of Ob	gulf	root	1	23347	2019-06-30 14:09:33.157392	\N	\N	100
state:Atyrau:609862	S2	Atyrau	state	country:Kazakhstan:1522867	1	21181	2019-06-30 19:48:51.890114	\N	\N	100
state:Vientiane:1904618	S2	Vientiane	state	country:Laos:1655842	1	3018	2019-07-01 14:57:35.896712	\N	\N	100
state:Ningxia:1799355	S2	Ningxia	state	region:NorthwestChina:	1	7425	2019-06-30 23:25:09.651609	\N	\N	100
state:Hiroshima:1862413	S2	Hiroshima	state	region:Chugoku:1864492	1	1579	2019-07-01 12:28:41.54149	\N	\N	100
state:Loei:1609070	S2	Loei	state	region:Northeastern:	1	510	2019-07-01 14:41:52.479066	\N	\N	100
state:Laghouat:2491188	S2	Laghouat	state	country:Algeria:2589581	1	4244	2019-06-30 09:51:53.074439	\N	\N	100
year:2023	S2	2023	year	root	1	3838989	2023-01-01 03:59:29.934239	\N	\N	100
state:Gabu:2372533	S2	Gabú	state	region:Leste:2371768	1	2552	2019-07-01 20:59:19.48212	\N	\N	100
region:Leste:2371768	S2	Leste	region	country:GuineaBissau:2372248	0	2553	2019-07-01 20:59:19.482671	\N	\N	100
country:Egypt:357994	S2	Egypt	country	continent:Africa:6255146	0	101701	2019-06-30 19:04:11.993456	\N	\N	100
state:Puebla:3521082	S2	Puebla	state	country:Mexico:3996063	1	5422	2019-07-02 11:18:00.467689	\N	\N	100
country:GuineaBissau:2372248	S2	Guinea-Bissau	country	continent:Africa:6255146	0	4578	2019-07-01 20:59:19.483186	\N	\N	100
state:Salamat:242048	S2	Salamat	state	country:Chad:2434508	1	8459	2019-07-02 16:28:24.695852	\N	\N	100
state:Coast:199247	S2	Coast	state	country:Kenya:192950	1	9383	2019-06-30 20:12:41.871798	\N	\N	100
state:Dornogovi:2031798	S2	Dornogovi	state	country:Mongolia:2029969	1	18939	2019-06-30 23:22:08.937191	\N	\N	100
state:Wardak:1121863	S2	Wardak	state	country:Afghanistan:1149361	1	1483	2019-07-01 15:15:37.386635	\N	\N	100
state:Vidin:864562	S2	Vidin	state	country:Bulgaria:732800	1	1015	2019-07-01 19:04:23.912146	\N	\N	100
state:Bamyan:1147239	S2	Bamyan	state	country:Afghanistan:1149361	1	3373	2019-07-01 15:15:37.387409	\N	\N	100
state:HaGiang:1581030	S2	Hà Giang	state	region:DongBac:11497248	1	1033	2019-07-03 11:12:28.354183	\N	\N	100
state:Uusimaa:830709	S2	Uusimaa	state	country:Finland:660013	1	6660	2019-06-30 17:57:25.561195	\N	\N	100
state:PaijatHame:831040	S2	Päijät-Häme	state	country:Finland:660013	1	3208	2019-06-30 18:01:01.216337	\N	\N	100
state:Kymenlaakso:830703	S2	Kymenlaakso	state	country:Finland:660013	1	2794	2019-06-30 10:10:36.305825	\N	\N	100
state:Sichuan:1794299	S2	Sichuan	state	region:SouthwestChina:	1	49518	2019-06-30 10:15:00.699969	\N	\N	100
state:SouthGeorgiaAndTheSouthSandwichIslands:3474415	S2	South Georgia and the South Sandwich Islands	state	country:SouthGeorgiaAndSouthSandwichIslands:3474415	1	2211	2019-07-10 15:54:09.168396	\N	\N	100
country:SouthGeorgiaAndSouthSandwichIslands:3474415	S2	South Georgia and South Sandwich Islands	country	continent:SevenSeas(OpenOcean):	0	2211	2019-07-10 15:54:09.168922	\N	\N	100
state:CarasSeverin:682714	S2	Caras-Severin	state	country:Romania:798549	1	2520	2019-07-01 18:57:00.785323	\N	\N	100
sea:SalishSea:7308121	S2	Salish Sea	sea	root	1	3653	2019-07-01 04:49:28.221724	\N	\N	100
state:Sinaloa:3983035	S2	Sinaloa	state	country:Mexico:3996063	1	8304	2019-07-01 05:25:48.016805	\N	\N	100
state:Branicevski:7581794	S2	Branicevski	state	region:Branicevski:7581794	1	503	2019-07-01 19:08:46.649699	\N	\N	100
state:Songkhla:1606146	S2	Songkhla	state	region:Southern:10234924	1	999	2019-07-01 15:16:38.772911	\N	\N	100
region:Branicevski:7581794	S2	Braničevski	region	country:Serbia:6290252	0	503	2019-07-01 19:08:46.650269	\N	\N	100
state:Ryanggang:2039332	S2	Ryanggang	state	country:NorthKorea:1873107	1	2579	2019-06-30 11:43:55.325007	\N	\N	100
state:Eysturoyar:2622387	S2	Eysturoyar	state	country:FaroeIslands:2622009	1	7701	2019-06-30 09:52:20.538947	\N	\N	100
country:FaroeIslands:2622009	S2	Faroe Islands	country	continent:Europe:6255148	0	7701	2019-06-30 09:52:20.539349	\N	\N	100
region:TayBac:11497246	S2	Tây Bắc	region	country:Vietnam:1562822	0	4091	2019-07-01 13:41:19.224586	\N	\N	100
state:Western:2194371	S2	Western	state	country:Fiji:2205218	1	1004	2019-07-02 09:10:15.376746	\N	\N	100
country:Thailand:1605651	S2	Thailand	country	continent:Asia:6255147	0	54589	2019-06-30 10:14:46.672738	\N	\N	100
state:Houaphan:1657114	S2	Houaphan	state	country:Laos:1655842	1	2573	2019-07-01 13:14:16.505333	\N	\N	100
state:Savannakhet:1653315	S2	Savannakhét	state	country:Laos:1655842	1	2579	2019-06-30 12:42:27.591828	\N	\N	100
state:Veracruz:3514780	S2	Veracruz	state	country:Mexico:3996063	1	10493	2019-07-01 07:57:40.446465	\N	\N	100
country:Fiji:2205218	S2	Fiji	country	continent:Oceania:6255151	0	2992	2019-07-02 09:10:15.377338	\N	\N	100
state:ThanhHoa:1566165	S2	Thanh Hóa	state	region:BacTrungBo:11497251	1	1016	2019-07-03 11:11:12.726693	\N	\N	100
state:Buryat:2050915	S2	Buryat	state	region:Siberian:11961345	1	56193	2019-06-30 13:30:14.539049	\N	\N	100
state:NakhonRatchasima:1608528	S2	Nakhon Ratchasima	state	region:Northeastern:	1	4062	2019-07-01 14:28:28.635802	\N	\N	100
region:Northeastern:	S2	Northeastern	region	country:Thailand:1605651	0	12471	2022-05-06 09:00:07.237368	\N	\N	100
state:SonLa:1567643	S2	Son La	state	region:TayBac:11497246	1	2061	2019-07-01 13:41:19.22412	\N	\N	100
gulf:PersianGulf:235616	S2	Persian Gulf	gulf	root	1	29191	2019-06-30 20:02:31.877094	\N	\N	100
state:Qyzylorda:1521406	S2	Qyzylorda	state	country:Kazakhstan:1522867	1	32821	2019-06-30 14:40:37.82009	\N	\N	100
state:WesternDarfur:408658	S2	Western Darfur	state	region:Darfur:408660	1	3133	2019-07-04 16:37:05.305129	\N	\N	100
state:Corum:748877	S2	Çorum	state	country:Turkey:298795	1	2076	2019-07-01 22:27:32.649308	\N	\N	100
region:NorthernGreatPlain:	S2	Northern Great Plain	region	country:Hungary:719819	0	3055	2019-07-01 18:53:33.294843	\N	\N	100
state:SouthKazakhstan:1524787	S2	South Kazakhstan	state	country:Kazakhstan:1522867	1	17650	2019-07-01 15:16:19.38502	\N	\N	100
state:Dodoma:160192	S2	Dodoma	state	country:Tanzania:149590	1	4161	2019-07-03 16:22:34.133834	\N	\N	100
state:Kastamonu:743881	S2	Kastamonu	state	country:Turkey:298795	1	2201	2019-07-01 22:44:12.519982	\N	\N	100
state:Northern:408667	S2	Northern	state	region:Northern:408667	1	41830	2019-06-30 16:09:34.36069	\N	\N	100
state:KhonKaen:1609775	S2	Khon Kaen	state	region:Northeastern:	1	2507	2019-07-01 16:25:50.998905	\N	\N	100
state:HajduBihar:720293	S2	Hajdú-Bihar	state	region:NorthernGreatPlain:	1	1030	2019-07-01 19:08:44.798082	\N	\N	100
state:CaoBang:1586182	S2	Cao Bằng	state	region:DongBac:11497248	1	520	2019-07-03 11:35:38.205972	\N	\N	100
state:BistritaNasaud:684647	S2	Bistrita-Nasaud	state	country:Romania:798549	1	2229	2019-07-01 19:00:46.8852	\N	\N	100
state:NorthKordufan:	S2	North Kordufan	state	region:Kordofan:408667	1	24332	2019-06-30 16:05:43.442324	\N	\N	100
state:ScottishBorders:2655192	S2	Scottish Borders	state	region:Eastern:2650458	1	1969	2019-07-02 20:20:06.059968	\N	\N	100
region:Shikoku:1852487	S2	Shikoku	region	country:Japan:1861060	0	2633	2019-07-01 12:25:21.290691	\N	\N	100
state:YenBai:1559978	S2	Yên Bái	state	region:DongBac:11497248	1	1531	2019-07-03 11:25:20.192496	\N	\N	100
state:Xaignabouri:1652210	S2	Xaignabouri	state	country:Laos:1655842	1	2040	2019-07-01 14:41:52.47823	\N	\N	100
state:Northumberland:2641235	S2	Northumberland	state	region:NorthEast:11591950	1	1474	2019-07-02 20:20:06.061022	\N	\N	100
region:Kordofan:408667	S2	Kordofan	region	country:Sudan:366755	0	39674	2019-06-30 16:05:43.442831	\N	\N	100
region:NorthEast:11591950	S2	North East	region	country:UnitedKingdom:2635167	0	2481	2019-07-01 19:46:21.331264	\N	\N	100
state:Maniema:209610	S2	Maniema	state	country:Congo:203312	1	14987	2019-06-30 15:49:14.15353	\N	\N	100
country:Guinea:2420477	S2	Guinea	country	continent:Africa:6255146	0	24128	2019-06-30 21:58:25.399003	\N	\N	100
state:Boke:8335085	S2	Boke	state	region:Boke:8335085	1	2347	2019-07-01 21:00:15.970603	\N	\N	100
state:Gansu:1810676	S2	Gansu	state	region:NorthwestChina:	1	54270	2019-06-30 13:31:53.193914	\N	\N	100
region:Boke:8335085	S2	Boke	region	country:Guinea:2420477	0	4892	2019-07-01 20:59:19.484134	\N	\N	100
state:Finistere:3018471	S2	Finistère	state	region:Bretagne:3030293	1	2035	2019-07-01 19:39:08.395558	\N	\N	100
state:Tatarstan:484048	S2	Tatarstan	state	region:Volga:11961325	1	9728	2019-07-01 15:40:51.938419	\N	\N	100
state:Chongqing:1814905	S2	Chongqing	state	region:SouthwestChina:	1	8246	2019-06-30 12:36:51.529473	\N	\N	100
state:SouthCarolina:4597040	S2	South Carolina	state	region:South:11887752	1	6792	2019-07-01 02:12:17.403547	\N	\N	100
state:Attapu:1665045	S2	Attapu	state	country:Laos:1655842	1	1028	2019-06-30 12:34:16.539589	\N	\N	100
state:Vientiane[Prefecture]:1904618	S2	Vientiane [prefecture]	state	country:Laos:1655842	1	1978	2019-07-01 15:03:39.053551	\N	\N	100
state:Utenos:864484	S2	Utenos	state	country:Lithuania:597427	1	2023	2019-07-01 18:51:40.681583	\N	\N	100
state:Champasak:1657818	S2	Champasak	state	country:Laos:1655842	1	1459	2019-06-30 12:42:37.231882	\N	\N	100
state:UdonThani:1906686	S2	Udon Thani	state	region:Northeastern:	1	2489	2019-07-01 15:46:38.992093	\N	\N	100
state:IslasDeLaBahia:3608814	S2	Islas de la Bahía	state	country:Honduras:3608932	1	1534	2019-07-02 02:07:26.465954	\N	\N	100
state:Centre:2233376	S2	Centre	state	country:Cameroon:2233387	1	8929	2019-07-01 18:51:40.557314	\N	\N	100
state:Tocantins:3474575	S2	Tocantins	state	country:Brazil:3469034	1	30784	2019-06-30 09:52:47.087983	\N	\N	100
state:Matruh:352603	S2	Matruh	state	country:Egypt:357994	1	17753	2019-07-01 21:32:50.871623	\N	\N	100
state:Voronezh:472039	S2	Voronezh	state	region:Central:11961322	1	7242	2019-06-30 19:02:34.655006	\N	\N	100
state:Oaxaca:3522509	S2	Oaxaca	state	country:Mexico:3996063	1	11698	2019-07-01 07:58:01.966958	\N	\N	100
state:ChaharMahallAndBakhtiari:139677	S2	Chahar Mahall and Bakhtiari	state	country:Iran:130758	1	1970	2019-07-02 14:20:46.233935	\N	\N	100
state:Kgalagadi:933657	S2	Kgalagadi	state	country:Botswana:933860	1	11178	2019-06-30 20:09:34.289981	\N	\N	100
state:Limpopo:1085597	S2	Limpopo	state	country:SouthAfrica:953987	1	17925	2019-07-01 17:01:25.784583	\N	\N	100
state:ThaiNguyen:1905497	S2	Thái Nguyên	state	region:DongB?C:	1	1212	2019-07-03 11:11:48.696702	\N	\N	100
state:NinhBinh:1559970	S2	Ninh Bình	state	region:DongBangSongHong:	1	500	2019-07-03 11:17:41.784584	\N	\N	100
state:MatabelelandSouth:886747	S2	Matabeleland South	state	country:Zimbabwe:878675	1	7106	2019-07-01 17:08:50.708047	\N	\N	100
region:DongB?C:	S2	Ðông B?c	region	country:Vietnam:1562822	0	1212	2019-07-03 11:11:48.697161	\N	\N	100
state:DongBac:1905669	S2	Đông Bắc	state	country:Vietnam:1562822	1	686	2019-07-03 11:15:55.674097	\N	\N	100
state:HoaBinh:1572594	S2	Hòa Bình	state	region:TayBac:11497246	1	999	2019-07-03 11:11:48.696238	\N	\N	100
state:Anhui:1818058	S2	Anhui	state	region:EastChina:6493581	1	15097	2019-07-01 00:31:43.289955	\N	\N	100
state:LangSon:1576632	S2	Lạng Sơn	state	region:DongBac:11497248	1	1045	2019-06-30 12:42:20.140263	\N	\N	100
state:Trarza:2375742	S2	Trarza	state	country:Mauritania:2378080	1	7387	2019-07-02 20:10:53.043279	\N	\N	100
state:NorthWest:1085598	S2	North West	state	country:SouthAfrica:953987	1	16226	2019-06-30 20:15:01.855687	\N	\N	100
state:Tehran:110791	S2	Tehran	state	country:Iran:130758	1	1718	2019-07-02 14:14:10.941953	\N	\N	100
state:Tlaxcala:3515359	S2	Tlaxcala	state	country:Mexico:3996063	1	1687	2019-07-02 11:18:00.466806	\N	\N	100
state:Mazandaran:124544	S2	Mazandaran	state	country:Iran:130758	1	3784	2019-07-02 14:14:10.941419	\N	\N	100
state:Blida:2503765	S2	Blida	state	country:Algeria:2589581	1	1171	2019-07-02 19:19:49.688734	\N	\N	100
state:LaUnion:3584767	S2	La Unión	state	country:ElSalvador:3585968	1	2077	2019-07-02 02:08:13.175051	\N	\N	100
state:Qom:443794	S2	Qom	state	country:Iran:130758	1	1025	2019-07-02 14:19:42.816044	\N	\N	100
state:Tipaza:2476027	S2	Tipaza	state	country:Algeria:2589581	1	1527	2019-07-02 18:27:51.5496	\N	\N	100
state:Palmerston:4036432	S2	Palmerston	state	country:CookIslands:1899402	1	442	2019-10-27 23:41:52.194794	\N	\N	100
state:Alger:2507475	S2	Alger	state	country:Algeria:2589581	1	672	2019-07-04 18:49:35.657558	\N	\N	100
state:Markazi:124763	S2	Markazi	state	country:Iran:130758	1	5569	2019-06-30 20:02:39.843297	\N	\N	100
state:LaPaz:3607251	S2	La Paz	state	country:Honduras:3608932	1	1045	2019-07-02 02:15:48.916727	\N	\N	100
state:Valle:3600456	S2	Valle	state	country:Honduras:3608932	1	1034	2019-07-02 02:08:13.1767	\N	\N	100
state:Comayagua:3613319	S2	Comayagua	state	country:Honduras:3608932	1	1038	2019-07-02 02:15:48.916049	\N	\N	100
state:FranciscoMorazan:3609672	S2	Francisco Morazán	state	country:Honduras:3608932	1	1057	2019-07-02 02:15:48.917414	\N	\N	100
state:Yoro:3600193	S2	Yoro	state	country:Honduras:3608932	1	1260	2019-07-02 02:15:19.904437	\N	\N	100
sound:SenoOtway:3877922	S2	Seno Otway	sound	root	1	3019	2019-06-30 10:14:34.733507	\N	\N	100
state:Gard:3016670	S2	Gard	state	region:LanguedocRoussillon:11071623	1	1576	2019-07-02 19:06:30.573392	\N	\N	100
sound:SenoDeSkyring	S2	Seno de Skyring	sound	root	1	2016	2019-06-30 10:14:34.732918	\N	\N	100
region:LanguedocRoussillon:11071623	S2	Languedoc-Roussillon	region	country:France:3017382	0	4589	2019-06-30 21:13:49.266935	\N	\N	100
state:BouchesDuRhone:3031359	S2	Bouches-du-Rhône	state	region:ProvenceAlpesCoteDAzur:2985244	1	1031	2019-07-02 19:06:30.574607	\N	\N	100
state:Atlantida:3615027	S2	Atlántida	state	country:Honduras:3608932	1	738	2019-07-02 02:15:19.903442	\N	\N	100
state:Cortes:3613140	S2	Cortés	state	country:Honduras:3608932	1	1554	2019-07-02 01:52:14.241001	\N	\N	100
state:Sverdlovsk:1490542	S2	Sverdlovsk	state	region:Urals:466003	1	35691	2019-06-30 18:41:38.220624	\N	\N	100
state:YamalNenets:1486462	S2	Yamal-Nenets	state	region:Urals:466003	1	162002	2019-06-30 14:03:43.382705	\N	\N	100
region:Iraq:99237	S2	Iraq	region	country:Iraq:99237	0	45460	2019-06-30 19:20:29.231754	\N	\N	100
state:Yaroslavl:468898	S2	Yaroslavl'	state	region:Central:11961322	1	5933	2019-07-01 19:40:11.225765	\N	\N	100
day:06	S2	06	day	root	1	932628	2019-07-06 10:21:11.998989	\N	\N	100
country:Iraq:99237	S2	Iraq	country	continent:Asia:6255147	0	48748	2019-06-30 19:20:29.232445	\N	\N	100
state:AlAnbar:99592	S2	Al-Anbar	state	region:Iraq:99237	1	16818	2019-07-01 16:11:49.393911	\N	\N	100
state:UlYanovsk:479119	S2	Ul'yanovsk	state	region:Volga:11961325	1	5562	2019-07-01 15:56:48.001685	\N	\N	100
state:Xiangkhoang:1652077	S2	Xiangkhoang	state	country:Laos:1655842	1	1529	2019-07-01 13:56:12.146737	\N	\N	100
state:Hiiraan:57060	S2	Hiiraan	state	country:Somalia:51537	1	3200	2019-07-04 14:01:18.466626	\N	\N	100
state:Bakool:64982	S2	Bakool	state	country:Somalia:51537	1	3636	2019-07-02 14:47:13.713824	\N	\N	100
state:AlJahrah:285798	S2	Al Jahrah	state	country:Kuwait:285570	1	2033	2019-06-30 19:20:28.860719	\N	\N	100
state:Samara:499068	S2	Samara	state	region:Volga:11961325	1	8909	2019-07-01 15:46:53.795438	\N	\N	100
state:DongBangSongHong:1905699	S2	Đồng Bằng Sông Hồng	state	country:Vietnam:1562822	1	1507	2019-07-03 11:11:48.695024	\N	\N	100
region:DongBangSongHong:	S2	Đồng Bằng Sông Hồng	region	country:Vietnam:1562822	0	2031	2019-06-30 12:41:09.68992	\N	\N	100
country:Kuwait:285570	S2	Kuwait	country	continent:Asia:6255147	0	4065	2019-06-30 19:20:28.861196	\N	\N	100
state:HaiDuong:1905686	S2	Hải Dương	state	region:DongBangSongHong:	1	1008	2019-06-30 13:01:32.301222	\N	\N	100
state:BacGiang:1905419	S2	Bắc Giang	state	region:DongBac:11497248	1	1002	2019-06-30 13:01:32.302175	\N	\N	100
state:Pennsylvania:6254927	S2	Pennsylvania	state	region:Northeast:11887749	1	16313	2019-07-01 01:43:52.270745	\N	\N	100
country:Kazakhstan:1522867	S2	Kazakhstan	country	continent:Asia:6255147	0	391787	2019-06-30 14:40:37.820562	\N	\N	100
state:Bihor:684878	S2	Bihor	state	country:Romania:798549	1	1516	2019-07-01 18:38:46.088783	\N	\N	100
state:MarlboroughDistrict:2187304	S2	Marlborough District	state	region:SouthIsland:2182504	1	4489	2019-07-01 07:40:33.430288	\N	\N	100
state:EastKazakhstan:1517381	S2	East Kazakhstan	state	country:Kazakhstan:1522867	1	48483	2019-06-30 16:37:37.538058	\N	\N	100
state:Nenets:1486462	S2	Nenets	state	region:Northwestern:11961321	1	49439	2019-06-30 16:57:32.423324	\N	\N	100
state:Perm:511180	S2	Perm'	state	region:Volga:11961325	1	31548	2019-06-30 18:44:26.915176	\N	\N	100
state:AlIskandariyah:361059	S2	Al Iskandariyah	state	country:Egypt:357994	1	1517	2019-07-01 21:37:42.845857	\N	\N	100
state:Angus:2657306	S2	Angus	state	region:Eastern:2650458	1	2469	2019-07-02 19:37:07.071597	\N	\N	100
state:Jamtland:2703330	S2	Jämtland	state	country:Sweden:2661886	1	14046	2019-06-30 09:52:01.253521	\N	\N	100
state:Makkah:104514	S2	Makkah	state	country:SaudiArabia:102358	1	16248	2019-07-01 16:11:44.277408	\N	\N	100
region:Eastern:2650458	S2	Eastern	region	country:UnitedKingdom:2635167	0	5065	2019-06-30 18:33:53.196734	\N	\N	100
state:Alabama:4829764	S2	Alabama	state	region:South:11887752	1	10795	2019-06-30 10:15:52.533347	\N	\N	100
state:Chhattisgarh:1444364	S2	Chhattisgarh	state	region:Central:8335421	1	12728	2019-06-30 15:21:25.474184	\N	\N	100
state:Niksic:	S2	Nikšic	state	country:Montenegro:3194884	1	1523	2019-07-01 18:26:56.604598	\N	\N	100
state:DubrovackoNeretvanska:3337513	S2	Dubrovacko-Neretvanska	state	country:Croatia:3202326	1	2063	2019-07-02 17:50:46.940144	\N	\N	100
state:ManawatuWanganui:2179671	S2	Manawatu-Wanganui	state	region:NorthIsland:2185983	1	2483	2019-07-01 07:47:41.330227	\N	\N	100
state:Tivat:3189071	S2	Tivat	state	country:Montenegro:3194884	1	2565	2019-07-04 16:17:58.935595	\N	\N	100
state:SamutSakhon:1606587	S2	Samut Sakhon	state	region:Central:10177180	1	496	2019-07-01 14:42:09.69663	\N	\N	100
state:Apure:3649151	S2	Apure	state	country:Venezuela:3625428	1	8833	2019-06-30 10:14:07.928437	\N	\N	100
sea:NorthSea:5129210	S2	North Sea	sea	root	1	103448	2019-06-30 09:51:57.844021	\N	\N	100
state:Vasternorrland:2664292	S2	Västernorrland	state	country:Sweden:2661886	1	8349	2019-07-02 01:26:38.946953	\N	\N	100
state:HercegNovi:3199393	S2	Herceg Novi	state	country:Montenegro:3194884	1	1026	2019-07-04 16:17:58.936112	\N	\N	100
state:Kotor:3197537	S2	Kotor	state	country:Montenegro:3194884	1	1540	2019-07-04 16:17:58.936579	\N	\N	100
state:Pluzine:3193129	S2	Plužine	state	country:Montenegro:3194884	1	1540	2019-07-04 16:17:58.937047	\N	\N	100
state:Trebinje:3344117	S2	Trebinje	state	region:RepuplikaSrpska:	1	519	2019-07-04 16:17:58.939037	\N	\N	100
state:Guizhou:1809445	S2	Guizhou	state	region:SouthwestChina:	1	18493	2019-06-30 12:38:18.480649	\N	\N	100
state:Hunan:1806691	S2	Hunan	state	region:SouthCentralChina:	1	19365	2019-06-30 12:36:41.190936	\N	\N	100
state:JammuAndKashmir:1269320	S2	Jammu and Kashmir	state	region:North:8335144	1	13673	2019-06-30 16:18:05.159043	\N	\N	100
state:Najran:103628	S2	Najran	state	country:SaudiArabia:102358	1	9378	2019-06-30 19:19:48.600727	\N	\N	100
state:Parwan:1131054	S2	Parwan	state	country:Afghanistan:1149361	1	991	2019-07-01 15:26:07.988877	\N	\N	100
state:Manus:2091495	S2	Manus	state	country:PapuaNewGuinea:2088628	1	1001	2019-07-01 09:53:21.862664	\N	\N	100
state:Western:2294076	S2	Western	state	country:Ghana:2300660	1	3417	2019-06-30 09:51:45.000777	\N	\N	100
state:Stavropol:487839	S2	Stavropol'	state	region:Volga:11961325	1	8578	2019-07-01 15:41:11.04689	\N	\N	100
state:NorthernSavonia:830690	S2	Northern Savonia	state	country:Finland:660013	1	5585	2019-06-30 10:10:38.714438	\N	\N	100
sea:IonianSea:10233754	S2	Ionian Sea	sea	root	1	26080	2019-06-30 19:05:29.760127	\N	\N	100
state:Comoe:2597322	S2	Comoe	state	country:IvoryCoast:2287781	1	525	2019-06-30 09:51:45.002192	\N	\N	100
state:ReggioCalabria:2523629	S2	Reggio Calabria	state	region:Calabria:2525468	1	1532	2019-07-02 17:57:43.420252	\N	\N	100
state:Neembucu:3437677	S2	Ñeembucú	state	country:Paraguay:3437598	1	3603	2019-07-02 03:14:24.694133	\N	\N	100
state:CuandoCubango:876337	S2	Cuando Cubango	state	country:Angola:3351879	1	22070	2019-06-30 09:51:38.926382	\N	\N	100
state:Siirt:300821	S2	Siirt	state	country:Turkey:298795	1	2505	2019-07-01 16:19:50.30377	\N	\N	100
state:SantiagoDelEstero:3835868	S2	Santiago del Estero	state	country:Argentina:3865483	1	13066	2019-06-30 10:10:27.59559	\N	\N	100
sea:BarentsSea:630674	S2	Barents Sea	sea	root	1	532348	2019-06-30 09:52:10.283022	\N	\N	100
bay:GeographeBay:2071030	S2	Geographe Bay	bay	root	1	1534	2019-07-01 13:15:12.043231	\N	\N	100
country:Ghana:2300660	S2	Ghana	country	continent:Africa:6255146	0	24931	2019-06-30 09:51:45.001616	\N	\N	100
state:Dihok:97270	S2	Dihok	state	region:Kurdistan:8429352	1	2991	2019-07-01 16:19:47.677264	\N	\N	100
state:Sirnak:443189	S2	Sirnak	state	country:Turkey:298795	1	2491	2019-07-01 16:19:49.041411	\N	\N	100
state:Bolama:2374689	S2	Bolama	state	region:Sul:2369151	1	516	2019-07-04 17:45:00.485865	\N	\N	100
day:23	S2	23	day	root	1	943020	2019-07-05 23:10:06.200402	\N	\N	100
state:Boqueron:3867442	S2	Boquerón	state	country:Paraguay:3437598	1	9183	2019-06-30 10:10:28.858643	\N	\N	100
state:Guarico:3640017	S2	Guárico	state	country:Venezuela:3625428	1	6511	2019-06-30 10:10:39.423996	\N	\N	100
country:Paraguay:3437598	S2	Paraguay	country	continent:SouthAmerica:6255150	0	47220	2019-06-30 10:10:28.8596	\N	\N	100
gulf:KhatangaGulf	S2	Khatanga Gulf	gulf	root	1	7293	2019-06-30 10:14:58.294442	\N	\N	100
state:Bandundu:2317396	S2	Bandundu	state	country:Congo:203312	1	28758	2019-07-01 23:34:17.69578	\N	\N	100
state:Arkansas:4099753	S2	Arkansas	state	region:South:11887752	1	15599	2019-07-01 02:50:59.481387	\N	\N	100
day:16	S2	16	day	root	1	925923	2019-07-16 04:27:41.129763	\N	\N	100
country:Laos:1655842	S2	Laos	country	continent:Asia:6255147	0	24807	2019-06-30 12:34:16.540085	\N	\N	100
day:10	S2	10	day	root	1	929477	2019-07-10 02:48:04.20008	\N	\N	100
state:Bolikhamxai:1904617	S2	Bolikhamxai	state	country:Laos:1655842	1	1730	2019-07-01 16:20:59.649313	\N	\N	100
state:Katanga:205703	S2	Katanga	state	country:Congo:203312	1	48675	2019-06-30 15:39:20.052059	\N	\N	100
state:SudKivu:205413	S2	Sud-Kivu	state	country:Congo:203312	1	8149	2019-06-30 16:07:41.193944	\N	\N	100
state:Kigoma:157732	S2	Kigoma	state	country:Tanzania:149590	1	9235	2019-07-02 15:32:40.428476	\N	\N	100
state:Golestan:443792	S2	Golestan	state	country:Iran:130758	1	4207	2019-07-01 18:39:24.163519	\N	\N	100
state:Erongo:3371199	S2	Erongo	state	country:Namibia:3355338	1	6382	2019-07-01 22:27:55.084246	\N	\N	100
state:MagallanesYAntarticaChilena:4036650	S2	Magallanes y Antártica Chilena	state	country:Chile:3895114	1	29114	2019-06-30 10:14:34.734005	\N	\N	100
state:NakhonPhanom:1608530	S2	Nakhon Phanom	state	region:Northeastern:	1	512	2019-07-03 11:29:46.389286	\N	\N	100
country:Chile:3895114	S2	Chile	country	continent:SouthAmerica:6255150	0	116072	2019-06-30 10:14:34.734529	\N	\N	100
state:Otjozondjupa:3371209	S2	Otjozondjupa	state	country:Namibia:3355338	1	13053	2019-06-30 09:51:38.491847	\N	\N	100
state:Khammouan:1656538	S2	Khammouan	state	region:Northeastern:	1	1569	2019-06-30 12:39:44.955707	\N	\N	100
state:Zinder:2437797	S2	Zinder	state	country:Niger:2440476	1	15672	2019-07-01 19:01:52.401475	\N	\N	100
state:NordurlandEystra:3337404	S2	Norðurland eystra	state	country:Iceland:2629691	1	6488	2019-07-01 20:41:49.75961	\N	\N	100
region:Northeastern:	S2	Northeastern	region	country:Laos:1655842	0	10747	2019-06-30 12:39:44.956184	\N	\N	100
state:Formosa:3433896	S2	Formosa	state	country:Argentina:3865483	1	13399	2019-06-30 10:10:28.855352	\N	\N	100
state:NgheAn:1559969	S2	Nghệ An	state	region:BacTrungBo:11497251	1	1027	2019-07-03 11:16:30.996759	\N	\N	100
state:KieNtem:2566981	S2	Kié-Ntem	state	country:EquatorialGuinea:2309096	1	1054	2019-07-01 19:15:56.546143	\N	\N	100
state:Sud:2221789	S2	Sud	state	country:Cameroon:2233387	1	6848	2019-07-01 18:59:12.401327	\N	\N	100
state:Bushehr:139816	S2	Bushehr	state	country:Iran:130758	1	3601	2019-07-02 13:25:21.959111	\N	\N	100
state:QuintanaRoo:3520887	S2	Quintana Roo	state	country:Mexico:3996063	1	7393	2019-06-30 10:16:19.449558	\N	\N	100
region:Eastern:8260673	S2	Eastern	region	country:Uganda:226074	0	6046	2019-07-01 16:59:06.759602	\N	\N	100
state:Katakwi:8657776	S2	Katakwi	state	region:Eastern:8260673	1	1014	2019-07-01 17:01:07.58956	\N	\N	100
country:Uganda:226074	S2	Uganda	country	continent:Africa:6255146	0	21548	2019-07-01 16:59:06.760092	\N	\N	100
state:AlManamah:290339	S2	Al Manāmah	state	country:Bahrain:290291	1	1046	2019-07-04 14:44:08.14431	\N	\N	100
state:KohgiluyehAndBuyerAhmad:126878	S2	Kohgiluyeh and Buyer Ahmad	state	country:Iran:130758	1	3092	2019-07-02 13:59:11.066422	\N	\N	100
state:Kurgan:1501312	S2	Kurgan	state	region:Urals:466003	1	12172	2019-06-30 14:41:42.259822	\N	\N	100
region:Northern:8260674	S2	Northern	region	country:Uganda:226074	0	9072	2019-07-01 17:01:07.590429	\N	\N	100
state:Napak:228032	S2	Napak	state	region:Northern:8260674	1	1025	2019-07-01 17:01:07.590005	\N	\N	100
state:AlWusta:290070	S2	Al Wusţá	state	country:Bahrain:290291	1	1038	2019-07-04 14:44:08.144896	\N	\N	100
state:AshShamaliyah:290112	S2	Ash Shamālīyah	state	country:Bahrain:290291	1	1042	2019-07-04 14:44:08.145489	\N	\N	100
state:AlJanubiyah:7090972	S2	Al Janūbīyah	state	country:Bahrain:290291	1	520	2019-07-04 14:50:56.078476	\N	\N	100
state:Manisa:304825	S2	Manisa	state	country:Turkey:298795	1	3759	2019-07-02 16:37:27.934684	\N	\N	100
state:Balikesir:322164	S2	Balikesir	state	country:Turkey:298795	1	2764	2019-07-02 16:35:44.896013	\N	\N	100
country:Bahrain:290291	S2	Bahrain	country	continent:Asia:6255147	0	1039	2019-07-04 14:44:08.146014	\N	\N	100
state:MadinatAchShamal:389462	S2	Madinat ach Shamal	state	country:Qatar:289688	1	684	2019-07-04 14:50:56.079436	\N	\N	100
state:ArRayyan:389469	S2	Ar Rayyān	state	country:Qatar:289688	1	1740	2019-07-04 14:49:41.87048	\N	\N	100
state:Wakayama:1848938	S2	Wakayama	state	region:Kinki:1859449	1	1032	2019-06-30 10:33:28.842383	\N	\N	100
region:Kinki:1859449	S2	Kinki	region	country:Japan:1861060	0	5010	2019-06-30 10:33:27.504091	\N	\N	100
state:Kracheh:1830563	S2	Krâchéh	state	country:Cambodia:1831722	1	1496	2019-06-30 12:33:56.698169	\N	\N	100
state:MondolKiri:1830306	S2	Môndól Kiri	state	country:Cambodia:1831722	1	3516	2019-06-30 12:33:56.736275	\N	\N	100
state:CentroSur:2566980	S2	Centro Sur	state	country:EquatorialGuinea:2309096	1	1056	2019-07-01 19:00:42.525159	\N	\N	100
country:EquatorialGuinea:2309096	S2	Equatorial Guinea	country	continent:Africa:6255146	0	2652	2019-07-01 18:55:47.370639	\N	\N	100
state:Yazd:111821	S2	Yazd	state	country:Iran:130758	1	17381	2019-07-01 18:06:15.118221	\N	\N	100
state:Amazonas:3649302	S2	Amazonas	state	country:Venezuela:3625428	1	19585	2019-06-30 10:10:30.015531	\N	\N	100
state:Liaoning:2036115	S2	Liaoning	state	region:NortheastChina:	1	23714	2019-06-30 11:39:12.224202	\N	\N	100
state:Michigan:5001836	S2	Michigan	state	region:Midwest:11887750	1	36018	2019-06-30 10:15:59.727599	\N	\N	100
state:Litoral:2566982	S2	Litoral	state	country:EquatorialGuinea:2309096	1	1058	2019-07-01 19:00:42.524693	\N	\N	100
state:Qostanay:1521671	S2	Qostanay	state	country:Kazakhstan:1522867	1	36426	2019-06-30 14:41:30.549223	\N	\N	100
state:ShabeellahaDhexe:51967	S2	Shabeellaha Dhexe	state	country:Somalia:51537	1	1228	2019-07-04 13:59:10.493859	\N	\N	100
country:Somalia:51537	S2	Somalia	country	continent:Africa:6255146	0	44763	2019-06-30 20:12:54.2179	\N	\N	100
state:ShabeellahaHoose:51966	S2	Shabeellaha Hoose	state	country:Somalia:51537	1	3805	2019-07-02 15:17:42.759905	\N	\N	100
state:Mugla:304183	S2	Mugla	state	country:Turkey:298795	1	3038	2019-07-02 16:42:11.162328	\N	\N	100
state:Bay:64538	S2	Bay	state	country:Somalia:51537	1	5248	2019-07-02 14:45:12.099737	\N	\N	100
state:AlJawf:109470	S2	Al Jawf	state	country:SaudiArabia:102358	1	14877	2019-06-30 19:17:21.867021	\N	\N	100
state:Chihuahua:4014336	S2	Chihuahua	state	country:Mexico:3996063	1	26969	2019-07-01 08:20:28.438924	\N	\N	100
state:Cuneo:3177699	S2	Cuneo	state	region:Piemonte:3170831	1	1601	2019-06-30 09:51:56.984865	\N	\N	100
state:Karakalpakstan:453752	S2	Karakalpakstan	state	country:Uzbekistan:1512440	1	25668	2019-06-30 14:55:52.457064	\N	\N	100
state:Kandal:1831095	S2	Kândal	state	country:Cambodia:1831722	1	1000	2019-06-30 12:43:14.972362	\N	\N	100
state:Cabanas:3587217	S2	Cabañas	state	country:ElSalvador:3585968	1	1031	2019-07-02 02:07:27.156174	\N	\N	100
state:HoChiMinhCity:1580578	S2	Hồ Chí Minh city	state	region:DongNamBo:11497301	1	1010	2019-06-30 12:58:39.136278	\N	\N	100
country:Cambodia:1831722	S2	Cambodia	country	continent:Asia:6255147	0	17273	2019-06-30 12:33:56.699086	\N	\N	100
state:HautesAlpes:3013738	S2	Hautes-Alpes	state	region:ProvenceAlpesCoteDAzur:2985244	1	1032	2019-07-04 18:47:37.991814	\N	\N	100
state:SanVicente:3583176	S2	San Vicente	state	country:ElSalvador:3585968	1	1559	2019-07-02 02:07:27.155666	\N	\N	100
state:Southern:896972	S2	Southern	state	country:Zambia:895949	1	7910	2019-06-30 15:39:29.339187	\N	\N	100
state:PreyVeng:1822609	S2	Prey Vêng	state	country:Cambodia:1831722	1	505	2019-06-30 12:43:47.738946	\N	\N	100
region:UturumaĕDaPalata:	S2	Uturumæ̆da paḷāta	region	country:SriLanka:1227603	0	1573	2019-06-30 15:53:42.36543	\N	\N	100
bay:GarabogazBay	S2	Garabogaz Bay	bay	root	1	4138	2019-07-02 14:15:08.637894	\N	\N	100
state:Semnan:116401	S2	Semnan	state	country:Iran:130758	1	13218	2019-07-01 18:20:44.469612	\N	\N	100
state:Konya:306569	S2	Konya	state	country:Turkey:298795	1	5595	2019-07-01 21:35:15.624128	\N	\N	100
state:Esfahan:418862	S2	Esfahan	state	country:Iran:130758	1	13940	2019-07-01 18:08:02.562935	\N	\N	100
state:Antalya:323776	S2	Antalya	state	country:Turkey:298795	1	5764	2019-07-01 21:28:40.621795	\N	\N	100
state:Trikunamalaya:1226258	S2	Trikuṇāmalaya	state	region:NaĕGenahiraPalata:	1	1576	2019-06-30 15:21:57.497828	\N	\N	100
country:SriLanka:1227603	S2	Sri Lanka	country	continent:Asia:6255147	0	7850	2019-06-30 15:18:53.223564	\N	\N	100
state:Vavuniyava:1225017	S2	Vavuniyāva	state	region:UturuPalata:	1	515	2019-06-30 15:53:42.363867	\N	\N	100
region:UturuPalata:	S2	Uturu paḷāta	region	country:SriLanka:1227603	0	1037	2019-06-30 15:53:42.364444	\N	\N	100
state:Anuradhapura:1251080	S2	Anurādhapura	state	region:UturumaĕDaPalata:	1	529	2019-06-30 15:53:42.364928	\N	\N	100
state:Okinawa:1854345	S2	Okinawa	state	region:Okinawa:1854345	1	1524	2019-07-01 03:53:16.854669	\N	\N	100
region:AutonomousRegionInMuslimMindanao(Armm):	S2	Autonomous Region in Muslim Mindanao (ARMM)	region	country:Philippines:1694008	0	1034	2019-07-01 10:42:07.634753	\N	\N	100
state:Central:933851	S2	Central	state	country:Botswana:933860	1	16763	2019-06-30 15:39:50.16226	\N	\N	100
sea:InnerSea	S2	Inner Sea	sea	root	1	6783	2019-06-30 10:33:37.85965	\N	\N	100
state:Lumbini:1283112	S2	Lumbini	state	region:West:1283118	1	2066	2019-06-30 15:36:55.881333	\N	\N	100
state:Oita:1854484	S2	Ōita	state	region:Kyushu:1857892	1	1056	2019-07-01 11:56:08.199821	\N	\N	100
country:Botswana:933860	S2	Botswana	country	continent:Africa:6255146	0	58123	2019-06-30 15:39:47.257779	\N	\N	100
region:Okinawa:1854345	S2	Okinawa	region	country:Japan:1861060	0	1523	2019-07-01 03:53:16.85511	\N	\N	100
state:LanaoDelSur:1707667	S2	Lanao del Sur	state	region:AutonomousRegionInMuslimMindanao(Armm):	1	511	2019-07-01 10:42:07.634258	\N	\N	100
country:Nepal:1282988	S2	Nepal	country	continent:Asia:6255147	0	18775	2019-06-30 15:35:55.749559	\N	\N	100
region:West:1283118	S2	West	region	country:Nepal:1282988	0	4649	2019-06-30 15:35:55.748132	\N	\N	100
state:Ehime:1864226	S2	Ehime	state	region:Shikoku:1852487	1	2042	2019-07-01 12:25:21.290202	\N	\N	100
state:Kgatleng:933654	S2	Kgatleng	state	country:Botswana:933860	1	1501	2019-07-02 17:27:15.275963	\N	\N	100
state:NewJersey:5101760	S2	New Jersey	state	region:Northeast:11887749	1	3867	2019-07-01 02:08:32.094113	\N	\N	100
country:Myanmar:1327865	S2	Myanmar	country	continent:Asia:6255147	0	78307	2019-06-30 10:14:46.408806	\N	\N	100
state:Mandalay:1311871	S2	Mandalay	state	country:Myanmar:1327865	1	8826	2019-06-30 10:14:46.407639	\N	\N	100
state:Shan:1297099	S2	Shan	state	country:Myanmar:1327865	1	20206	2019-06-30 10:14:46.408268	\N	\N	100
region:Polog:786909	S2	Polog	region	country:Macedonia:718075	0	1484	2019-07-01 18:37:40.41273	\N	\N	100
country:Macedonia:718075	S2	Macedonia	country	continent:Europe:6255148	0	5464	2019-07-01 18:31:00.864687	\N	\N	100
state:SoussMassaDraa:	S2	Souss - Massa - Draâ	state	country:Morocco:2542007	1	9341	2019-07-01 20:51:18.055942	\N	\N	100
region:North:8335144	S2	North	region	country:India:6269134	0	24051	2019-06-30 16:18:05.159832	\N	\N	100
state:RioNegro:3838830	S2	Río Negro	state	country:Argentina:3865483	1	31282	2019-06-30 10:14:24.070466	\N	\N	100
state:Imperia:3175531	S2	Imperia	state	region:Liguria:3174725	1	2020	2019-06-30 09:51:56.690018	\N	\N	100
state:Rosoman:862973	S2	Rosoman	state	country:Macedonia:718075	1	1924	2019-07-01 19:00:20.016703	\N	\N	100
state:Western:896140	S2	Western	state	country:Zambia:895949	1	17930	2019-06-30 15:39:29.339643	\N	\N	100
region:Extremadura:2593112	S2	Extremadura	region	country:Spain:2510769	0	9172	2019-07-01 19:13:48.789779	\N	\N	100
state:Vranestica:863913	S2	Vraneštica	state	region:Southwestern:9072896	1	872	2019-07-01 19:03:49.202916	\N	\N	100
state:Brod:863877	S2	Brod	state	region:Polog:786909	1	1920	2019-07-01 19:00:20.017146	\N	\N	100
region:Vardar:833492	S2	Vardar	region	country:Macedonia:718075	0	2404	2019-07-01 19:00:20.019183	\N	\N	100
state:Punjab:1259223	S2	Punjab	state	region:North:8335144	1	5670	2019-07-02 13:32:41.916347	\N	\N	100
state:Caceres:2520610	S2	Cáceres	state	region:Extremadura:2593112	1	5562	2019-07-01 19:13:48.789306	\N	\N	100
state:Pcinjski:7581802	S2	Pcinjski	state	region:Pcinjski:7581802	1	3136	2019-07-01 18:31:03.030229	\N	\N	100
state:Monaco:2993458	S2	Monaco	state	country:Monaco:2993458	1	1011	2019-06-30 09:51:56.687696	\N	\N	100
sea:KaraSea:1504324	S2	Kara Sea	sea	root	1	394381	2019-06-30 14:02:46.169359	\N	\N	100
country:Monaco:2993458	S2	Monaco	country	continent:Europe:6255148	0	1011	2019-06-30 09:51:56.688921	\N	\N	100
state:AlpesMaritimes:3038049	S2	Alpes-Maritimes	state	region:ProvenceAlpesCoteDAzur:2985244	1	1020	2019-06-30 09:51:56.691049	\N	\N	100
state:Saraj:863894	S2	Saraj	state	region:GreaterSkopje:	1	975	2019-07-01 19:03:49.20669	\N	\N	100
region:GreaterSkopje:	S2	Greater Skopje	region	country:Macedonia:718075	0	980	2019-07-01 19:03:49.20715	\N	\N	100
state:Dolneni:863849	S2	Dolneni	state	region:Pelagonia:	1	982	2019-07-01 19:03:49.205225	\N	\N	100
region:Pcinjski:7581802	S2	Pčinjski	region	country:Serbia:6290252	0	3071	2019-07-01 18:31:03.030838	\N	\N	100
state:Skopje:863886	S2	Skopje	state	country:Macedonia:718075	1	982	2019-07-01 19:03:49.206217	\N	\N	100
state:Studenicani:863903	S2	Studeničani	state	region:Skopje:785841	1	977	2019-07-01 19:03:49.207618	\N	\N	100
state:Caska:863838	S2	Čaška	state	region:Vardar:833492	1	979	2019-07-01 19:03:49.210774	\N	\N	100
day:13	S2	13	day	root	1	928003	2019-07-13 03:06:53.301993	\N	\N	100
day:19	S2	19	day	root	1	954948	2019-07-19 09:47:17.227973	\N	\N	100
state:Ennedi:7603256	S2	Ennedi	state	country:Chad:2434508	1	18009	2019-06-30 17:33:42.266873	\N	\N	100
gulf:GolfoDePanama:3703438	S2	Golfo de Panamá	gulf	root	1	3032	2019-06-30 10:15:17.31357	\N	\N	100
state:Transcarpathia:687869	S2	Transcarpathia	state	country:Ukraine:690791	1	3549	2019-07-01 18:40:45.077284	\N	\N	100
state:MilneBay:2132895	S2	Milne Bay	state	country:PapuaNewGuinea:2088628	1	2549	2019-06-30 10:32:15.48588	\N	\N	100
state:LViv:702550	S2	L'viv	state	country:Ukraine:690791	1	5075	2019-07-01 18:34:17.142848	\N	\N	100
state:Brest:629631	S2	Brest	state	country:Belarus:630336	1	10467	2019-07-01 18:51:41.670867	\N	\N	100
state:NorthernOstrobothnia:830667	S2	Northern Ostrobothnia	state	country:Finland:660013	1	9957	2019-06-30 18:16:44.777665	\N	\N	100
state:Grodno:828847	S2	Grodno	state	country:Belarus:630336	1	8730	2019-07-01 18:58:05.855373	\N	\N	100
state:Kainuu:830664	S2	Kainuu	state	country:Finland:660013	1	5440	2019-06-30 10:14:17.155015	\N	\N	100
state:WesternCape:1085599	S2	Western Cape	state	country:SouthAfrica:953987	1	17883	2019-06-30 20:09:00.098587	\N	\N	100
state:ArRiyad:108411	S2	Ar Riyad	state	country:SaudiArabia:102358	1	39815	2019-06-30 19:19:52.258207	\N	\N	100
state:UpperDemeraraBerbice:3375463	S2	Upper Demerara-Berbice	state	country:Guyana:3378535	1	7925	2019-06-30 23:41:31.280087	\N	\N	100
state:Dagestan:567293	S2	Dagestan	state	region:Volga:11961325	1	7219	2019-07-01 15:41:31.477589	\N	\N	100
state:Pavlodar:1520239	S2	Pavlodar	state	country:Kazakhstan:1522867	1	25479	2019-06-30 16:37:37.537418	\N	\N	100
state:Vaud:2658182	S2	Vaud	state	country:Switzerland:2658434	1	2060	2019-07-02 19:03:32.105046	\N	\N	100
state:Bayanhongor:1516290	S2	Bayanhongor	state	country:Mongolia:2029969	1	16105	2019-06-30 13:30:54.41478	\N	\N	100
state:Bern:2661551	S2	Bern	state	country:Switzerland:2658434	1	2535	2019-07-02 01:46:00.142827	\N	\N	100
state:Fribourg:2660717	S2	Fribourg	state	country:Switzerland:2658434	1	1025	2019-07-04 18:55:17.636446	\N	\N	100
state:AlMarqab:7602691	S2	Al Marqab	state	country:Libya:2215636	1	3149	2019-07-02 17:50:33.520049	\N	\N	100
state:Concepcion:3438833	S2	Concepción	state	country:Paraguay:3437598	1	3129	2019-07-02 04:38:11.584521	\N	\N	100
state:TajuraWaAnNawahiAlArba:2210245	S2	Tajura' wa an Nawahi al Arba	state	country:Libya:2215636	1	1066	2019-07-02 18:04:39.612531	\N	\N	100
state:Neuchatel:2659495	S2	Neuchâtel	state	country:Switzerland:2658434	1	2558	2019-07-02 19:04:31.61796	\N	\N	100
state:Valais:2658205	S2	Valais	state	country:Switzerland:2658434	1	2034	2019-07-02 01:46:00.143526	\N	\N	100
country:Switzerland:2658434	S2	Switzerland	country	continent:Europe:6255148	0	7955	2019-06-30 09:51:57.479891	\N	\N	100
state:HauteSavoie:3013736	S2	Haute-Savoie	state	region:RhoneAlpes:11071625	1	1567	2019-07-02 19:18:46.686616	\N	\N	100
state:Northern:2089478	S2	Northern	state	country:PapuaNewGuinea:2088628	1	2987	2019-07-01 09:54:30.937856	\N	\N	100
state:CityOfMinsk:625143	S2	City of Minsk	state	country:Belarus:630336	1	971	2019-07-01 19:18:04.227135	\N	\N	100
state:Nidwalden:2659471	S2	Nidwalden	state	country:Switzerland:2658434	1	2034	2019-06-30 09:51:57.465701	\N	\N	100
state:Glarus:2660593	S2	Glarus	state	country:Switzerland:2658434	1	3760	2019-06-30 09:51:57.472135	\N	\N	100
state:Obwalden:2659315	S2	Obwalden	state	country:Switzerland:2658434	1	2027	2019-06-30 09:51:57.464638	\N	\N	100
state:BadenWurttemberg:2953481	S2	Baden-Württemberg	state	country:Germany:2921044	1	4400	2019-06-30 09:51:57.462503	\N	\N	100
state:Uri:2658226	S2	Uri	state	country:Switzerland:2658434	1	2029	2019-06-30 09:51:57.468907	\N	\N	100
state:Zug:2657907	S2	Zug	state	country:Switzerland:2658434	1	1017	2019-06-30 09:51:57.466767	\N	\N	100
state:Schaffhausen:2658760	S2	Schaffhausen	state	country:Switzerland:2658434	1	1022	2019-06-30 09:51:57.469995	\N	\N	100
state:SanktGallen:2658821	S2	Sankt Gallen	state	country:Switzerland:2658434	1	1839	2019-06-30 09:51:57.473223	\N	\N	100
state:Thurgau:2658372	S2	Thurgau	state	country:Switzerland:2658434	1	1833	2019-06-30 09:51:57.474326	\N	\N	100
state:Schwyz:2658664	S2	Schwyz	state	country:Switzerland:2658434	1	1021	2019-06-30 09:51:57.475433	\N	\N	100
state:Aargau:2661876	S2	Aargau	state	country:Switzerland:2658434	1	1046	2019-06-30 09:51:57.477651	\N	\N	100
state:Lucerne:2659810	S2	Lucerne	state	country:Switzerland:2658434	1	1025	2019-06-30 09:51:57.476545	\N	\N	100
state:Zurich:2657895	S2	Zürich	state	country:Switzerland:2658434	1	1053	2019-06-30 09:51:57.47876	\N	\N	100
state:BaselLandschaft:2661603	S2	Basel-Landschaft	state	country:Switzerland:2658434	1	1033	2019-06-30 09:51:57.471071	\N	\N	100
state:Solothurn:2658563	S2	Solothurn	state	country:Switzerland:2658434	1	1030	2019-06-30 09:51:57.46785	\N	\N	100
state:SultanKudarat:1685377	S2	Sultan Kudarat	state	region:Soccsksargen(RegionXii):	1	512	2019-07-01 10:43:24.339497	\N	\N	100
state:JervisBayTerritory:8335033	S2	Jervis Bay Territory	state	country:Australia:2077456	1	1000	2019-06-30 13:50:35.826873	\N	\N	100
state:Maguindanao:1703701	S2	Maguindanao	state	region:AutonomousRegionInMuslimMindanao(Armm):	1	524	2019-07-01 10:43:24.340384	\N	\N	100
state:Valkas:454571	S2	Valkas	state	region:Vidzeme:7639661	1	1005	2019-06-30 10:10:35.181766	\N	\N	100
state:Voru:587448	S2	Võru	state	country:Estonia:453733	1	3522	2019-06-30 10:10:26.522073	\N	\N	100
state:AlWadiAtJadid:360483	S2	Al Wadi at Jadid	state	country:Egypt:357994	1	47821	2019-06-30 19:06:28.427356	\N	\N	100
state:Tartu:588334	S2	Tartu	state	country:Estonia:453733	1	1969	2019-06-30 10:10:26.522475	\N	\N	100
state:JubbadaDhexe:56084	S2	Jubbada Dhexe	state	country:Somalia:51537	1	3678	2019-06-30 20:52:19.027793	\N	\N	100
state:JubbadaHoose:56083	S2	Jubbada Hoose	state	country:Somalia:51537	1	5472	2019-06-30 20:12:54.217298	\N	\N	100
state:Jogeva:591901	S2	Jõgeva	state	country:Estonia:453733	1	1952	2019-06-30 10:10:35.182448	\N	\N	100
state:Viljandi:587590	S2	Viljandi	state	country:Estonia:453733	1	3042	2019-06-30 10:10:35.184716	\N	\N	100
state:Polva:589373	S2	Põlva	state	country:Estonia:453733	1	2001	2019-06-30 10:10:26.522832	\N	\N	100
country:Estonia:453733	S2	Estonia	country	continent:Europe:6255148	0	11339	2019-06-30 10:10:26.523202	\N	\N	100
state:Valga:587875	S2	Valga	state	country:Estonia:453733	1	1008	2019-06-30 10:10:35.184151	\N	\N	100
state:ElBeni:3923172	S2	El Beni	state	country:Bolivia:3923057	1	23228	2019-07-01 01:36:46.751405	\N	\N	100
state:Kharkiv:706482	S2	Kharkiv	state	country:Ukraine:690791	1	7213	2019-07-01 22:34:49.256752	\N	\N	100
state:Kano:2335196	S2	Kano	state	country:Nigeria:2328926	1	2570	2019-07-02 18:31:16.363445	\N	\N	100
state:Poltava:696634	S2	Poltava	state	country:Ukraine:690791	1	6039	2019-07-01 22:37:07.72969	\N	\N	100
state:Bohol:1724395	S2	Bohol	state	region:CentralVisayas(RegionVii):	1	1545	2019-06-30 14:14:05.263499	\N	\N	100
state:SaoTome:2410764	S2	São Tomé	state	country:SaoTomeAndPrincipe:2410758	1	1011	2019-07-04 16:20:31.441799	\N	\N	100
state:HaApai:4032637	S2	Ha'apai	state	country:Tonga:4032283	1	1465	2019-06-30 10:18:24.287747	\N	\N	100
state:Abkhazia:6643410	S2	Abkhazia	state	country:Georgia:614540	1	2532	2019-06-30 19:03:23.982746	\N	\N	100
country:SaoTomeAndPrincipe:2410758	S2	São Tomé and Príncipe	country	continent:Africa:6255146	0	1011	2019-07-04 16:20:31.442337	\N	\N	100
state:Uttaranchal:1444366	S2	Uttaranchal	state	region:Central:8335421	1	6809	2019-07-01 15:48:42.78757	\N	\N	100
state:Sremski:3190101	S2	Sremski	state	country:Serbia:6290252	1	2087	2019-07-01 18:36:47.853171	\N	\N	100
state:KalimantanSelatan:1641899	S2	Kalimantan Selatan	state	country:Indonesia:1643084	1	5156	2019-06-30 11:58:37.966422	\N	\N	100
state:OsjeckoBaranjska:3337522	S2	Osjecko-Baranjska	state	country:Croatia:3202326	1	2070	2019-07-02 17:48:57.468192	\N	\N	100
country:Croatia:3202326	S2	Croatia	country	continent:Europe:6255148	0	12632	2019-06-30 18:01:16.448769	\N	\N	100
state:VukovarskoSrijemska:3337529	S2	Vukovarsko-Srijemska	state	country:Croatia:3202326	1	1556	2019-07-04 16:14:33.455946	\N	\N	100
state:Crimea:703883	S2	Crimea	state	region:Volga:11961325	1	4669	2019-07-01 22:36:21.713757	\N	\N	100
state:ZapadnoBacki:7581911	S2	Zapadno-Backi	state	region:ZapadnoBacki:	1	516	2019-07-04 16:14:33.458871	\N	\N	100
region:ZapadnoBacki:	S2	Zapadno-Bački	region	country:Serbia:6290252	0	516	2019-07-04 16:14:33.459328	\N	\N	100
state:Rotanokiri:1822449	S2	Rôtânôkiri	state	country:Cambodia:1831722	1	2069	2019-06-30 12:38:52.52868	\N	\N	100
state:NewfoundlandAndLabrador:6354959	S2	Newfoundland and Labrador	state	region:EasternCanada:	1	69745	2019-06-30 10:10:24.253453	\N	\N	100
state:Tibesti:7603258	S2	Tibesti	state	country:Chad:2434508	1	16000	2019-06-30 16:55:04.044268	\N	\N	100
state:AlMadinah:109224	S2	Al Madinah	state	country:SaudiArabia:102358	1	15745	2019-07-01 16:10:02.866114	\N	\N	100
state:Dhawalagiri:1283454	S2	Dhawalagiri	state	region:West:1283118	1	2049	2019-06-30 15:37:43.628549	\N	\N	100
state:Cankiri:749747	S2	Çankiri	state	country:Turkey:298795	1	2203	2019-07-01 22:36:02.307201	\N	\N	100
state:AlJizah:360997	S2	Al Jizah	state	country:Egypt:357994	1	7701	2019-07-01 21:23:53.747186	\N	\N	100
state:Gedo:58802	S2	Gedo	state	country:Somalia:51537	1	6282	2019-06-30 20:13:23.147202	\N	\N	100
state:Samar:1690649	S2	Samar	state	region:EasternVisayas(RegionViii):	1	1029	2019-07-01 10:39:52.616749	\N	\N	100
state:HautMbomou:238639	S2	Haut-Mbomou	state	country:CentralAfricanRepublic:239880	1	7780	2019-07-01 21:28:02.617266	\N	\N	100
state:BarhElGazel:7603255	S2	Barh El Gazel	state	country:Chad:2434508	1	7400	2019-06-30 16:55:12.744965	\N	\N	100
state:PYonganNamdo:1871952	S2	P'yŏngan-namdo	state	country:NorthKorea:1873107	1	4120	2019-06-30 11:37:06.358642	\N	\N	100
state:ChagangDo:2045265	S2	Chagang-do	state	country:NorthKorea:1873107	1	5126	2019-06-30 11:37:49.005564	\N	\N	100
country:CentralAfricanRepublic:239880	S2	Central African Republic	country	continent:Africa:6255146	0	57338	2019-06-30 17:59:55.577599	\N	\N	100
state:Oromiya:444185	S2	Oromiya	state	country:Ethiopia:337996	1	37028	2019-06-30 20:18:46.529186	\N	\N	100
day:27	S2	27	day	root	1	942923	2019-06-30 10:10:20.297088	\N	\N	100
country:NorthKorea:1873107	S2	North Korea	country	continent:Asia:6255147	0	18753	2019-06-30 11:24:07.55877	\N	\N	100
state:PYonganBukto:1871954	S2	P'yŏngan-bukto	state	country:NorthKorea:1873107	1	2048	2019-06-30 11:37:49.004892	\N	\N	100
state:AlWusta:411737	S2	Al Wusta	state	country:Oman:286963	1	7220	2019-06-30 15:47:01.513266	\N	\N	100
state:BaminguiBangoran:240591	S2	Bamingui-Bangoran	state	country:CentralAfricanRepublic:239880	1	5612	2019-07-02 16:10:32.353823	\N	\N	100
country:Ethiopia:337996	S2	Ethiopia	country	continent:Africa:6255146	0	103520	2019-06-30 20:11:16.400031	\N	\N	100
state:Somali:444186	S2	Somali	state	country:Ethiopia:337996	1	36254	2019-06-30 20:11:16.399216	\N	\N	100
state:SistanAndBaluchestan:1159456	S2	Sistan and Baluchestan	state	country:Iran:130758	1	20947	2019-06-30 14:46:06.968256	\N	\N	100
lagoon:LagoaDosPatos:6321513	S2	Lagoa dos Patos	lagoon	root	1	2110	2019-06-30 09:52:32.411846	\N	\N	100
state:RioGrandeDoSul:3451133	S2	Rio Grande do Sul	state	country:Brazil:3469034	1	28813	2019-06-30 09:52:32.412412	\N	\N	100
state:Orel:514801	S2	Orel	state	region:Central:11961322	1	4164	2019-07-01 19:18:43.428652	\N	\N	100
day:15	S2	15	day	root	1	903976	2019-07-15 04:08:59.664482	\N	\N	100
state:Lublin:858785	S2	Lublin	state	country:Poland:798544	1	3713	2019-07-01 18:51:40.508464	\N	\N	100
state:Beja:2270984	S2	Beja	state	region:Alentejo:2268252	1	1542	2019-07-01 19:36:55.065187	\N	\N	100
state:Diffa:2445702	S2	Diffa	state	country:Niger:2440476	1	19505	2019-07-01 18:48:42.776232	\N	\N	100
region:Andalucia:2593109	S2	Andalucía	region	country:Spain:2510769	0	12759	2019-06-30 22:39:32.042039	\N	\N	100
country:Poland:798544	S2	Poland	country	continent:Europe:6255148	0	54252	2019-06-30 17:58:15.393281	\N	\N	100
state:WadiFira:244877	S2	Wadi Fira	state	country:Chad:2434508	1	6706	2019-07-02 16:42:23.978833	\N	\N	100
state:Burdur:320390	S2	Burdur	state	country:Turkey:298795	1	2253	2019-07-01 21:43:56.736675	\N	\N	100
state:Faro:2268337	S2	Faro	state	region:Algarve:2271989	1	2032	2019-07-01 19:31:34.653481	\N	\N	100
region:Algarve:2271989	S2	Algarve	region	country:Portugal:2264397	0	2032	2019-07-01 19:31:34.654017	\N	\N	100
state:Huelva:2516547	S2	Huelva	state	region:Andalucia:2593109	1	1017	2019-07-01 19:38:53.541787	\N	\N	100
day:14	S2	14	day	root	1	923257	2019-07-14 02:59:21.577898	\N	\N	100
state:Bukidnon:1723105	S2	Bukidnon	state	region:NorthernMindanao(RegionX):	1	1050	2019-07-01 10:36:41.359667	\N	\N	100
sea:Kattegat:9367730	S2	Kattegat	sea	root	1	7759	2019-06-30 09:52:00.545197	\N	\N	100
state:DavaoDelNorte:1715347	S2	Davao del Norte	state	region:Davao(RegionXi):	1	508	2019-07-01 10:38:03.582485	\N	\N	100
state:Khuzestan:127082	S2	Khuzestan	state	country:Iran:130758	1	8658	2019-06-30 19:23:35.374408	\N	\N	100
country:UnitedKingdom:2635167	S2	United Kingdom	country	continent:Europe:6255148	0	52950	2019-06-30 18:32:33.466787	\N	\N	100
state:Jonkoping:2702976	S2	Jönköping	state	country:Sweden:2661886	1	3611	2019-06-30 09:52:04.122709	\N	\N	100
state:KommuneqarfikSermersooq:7602007	S2	Kommuneqarfik Sermersooq	state	country:Greenland:3425505	1	138369	2019-06-30 09:52:24.415653	\N	\N	100
state:RedcarAndCleveland:3333186	S2	Redcar and Cleveland	state	region:NorthEast:11591950	1	1007	2019-07-01 19:37:10.01374	\N	\N	100
state:NorthYorkshire:2641209	S2	North Yorkshire	state	region:YorkshireAndTheHumber:11591951	1	2539	2019-07-01 19:37:10.014248	\N	\N	100
state:Kronoberg:2699050	S2	Kronoberg	state	country:Sweden:2661886	1	4040	2019-07-02 01:50:57.572737	\N	\N	100
gulf:GulfOfSidra:2210552	S2	Gulf of Sidra	gulf	root	1	9857	2019-07-01 18:27:47.637186	\N	\N	100
region:YorkshireAndTheHumber:11591951	S2	Yorkshire and the Humber	region	country:UnitedKingdom:2635167	0	4063	2019-07-01 19:34:46.985193	\N	\N	100
state:Shimane:1852442	S2	Shimane	state	region:Chugoku:1864492	1	1564	2019-07-01 12:46:38.647702	\N	\N	100
state:Trengganu:1733036	S2	Trengganu	state	country:Malaysia:1733045	1	1681	2019-06-30 12:47:15.845598	\N	\N	100
country:Malaysia:1733045	S2	Malaysia	country	continent:Asia:6255147	0	36535	2019-06-30 11:54:37.100919	\N	\N	100
sea:NorwegianSea:2960847	S2	Norwegian Sea	sea	root	1	195511	2019-06-30 09:52:09.568606	\N	\N	100
state:MarrakechTensiftAlHaouz:2542995	S2	Marrakech - Tensift - Al Haouz	state	country:Morocco:2542007	1	4615	2019-07-01 20:58:54.605747	\N	\N	100
state:Moskovsskaya:524925	S2	Moskovsskaya	state	region:Central:11961322	1	7069	2019-07-01 19:41:38.408724	\N	\N	100
sea:BlackSea:10922499	S2	Black Sea	sea	root	1	61821	2019-06-30 19:03:23.982249	\N	\N	100
state:Bartin:862467	S2	Bartın	state	country:Turkey:298795	1	1009	2019-07-01 22:36:02.306528	\N	\N	100
state:Badajoz:2521419	S2	Badajoz	state	region:Extremadura:2593112	1	3607	2019-07-01 19:33:28.590822	\N	\N	100
region:RhoneAlpes:11071625	S2	Rhône-Alpes	region	country:France:3017382	0	7456	2019-06-30 09:51:55.045134	\N	\N	100
region:Piemonte:3170831	S2	Piemonte	region	country:Italy:3175395	0	4115	2019-06-30 09:51:56.985955	\N	\N	100
state:Aoste:3182996	S2	Aoste	state	region:ValleDAosta:3164857	1	1013	2019-07-02 01:59:45.086389	\N	\N	100
region:ValleDAosta:3164857	S2	Valle d'Aosta	region	country:Italy:3175395	0	1013	2019-07-02 01:59:45.087032	\N	\N	100
state:Turin:3165523	S2	Turin	state	region:Piemonte:3170831	1	1030	2019-07-02 01:59:45.087614	\N	\N	100
country:Italy:3175395	S2	Italy	country	continent:Europe:6255148	0	49171	2019-06-30 09:51:56.986997	\N	\N	100
state:Savoie:2975517	S2	Savoie	state	region:RhoneAlpes:11071625	1	1022	2019-07-04 18:54:47.463943	\N	\N	100
country:Kyrgyzstan:1527747	S2	Kyrgyzstan	country	continent:Asia:6255147	0	33786	2019-06-30 16:17:15.271339	\N	\N	100
state:Subcarpathian:858788	S2	Subcarpathian	state	country:Poland:798544	1	4056	2019-07-01 18:26:40.240241	\N	\N	100
state:Osh:1346798	S2	Osh	state	country:Kyrgyzstan:1527747	1	7488	2019-06-30 16:17:15.270823	\N	\N	100
state:Kosicky:865084	S2	Košický	state	country:Slovakia:3057568	1	3021	2019-07-01 18:26:40.241624	\N	\N	100
state:Presov:865085	S2	Prešov	state	country:Slovakia:3057568	1	1789	2019-07-01 18:26:40.242359	\N	\N	100
country:Slovakia:3057568	S2	Slovakia	country	continent:Europe:6255148	0	9247	2019-06-30 18:18:54.313537	\N	\N	100
state:CaMau:1905678	S2	Cà Mau	state	region:DongBangSongCuuLong:1574717	1	1482	2019-06-30 12:40:13.433787	\N	\N	100
region:DongBangSongCuuLong:1574717	S2	đồng bằng sông Cửu Long	region	country:Vietnam:1562822	0	3552	2019-06-30 12:40:13.434323	\N	\N	100
state:Chubut:3861244	S2	Chubut	state	country:Argentina:3865483	1	34584	2019-06-30 10:14:17.581299	\N	\N	100
state:Covasna:680428	S2	Covasna	state	country:Romania:798549	1	3097	2019-06-30 18:40:54.430204	\N	\N	100
state:Mures:672628	S2	Mures	state	country:Romania:798549	1	1029	2019-07-03 17:08:38.580724	\N	\N	100
state:Harghita:676309	S2	Harghita	state	country:Romania:798549	1	2579	2019-06-30 18:40:54.430707	\N	\N	100
state:SulawesiTenggara:1626230	S2	Sulawesi Tenggara	state	country:Indonesia:1643084	1	4332	2019-07-01 12:00:52.688117	\N	\N	100
gulf:GulfOfFinland:453749	S2	Gulf of Finland	gulf	root	1	10043	2019-06-30 10:13:57.121417	\N	\N	100
state:Zamora:3104341	S2	Zamora	state	region:CastillaYLeon:3336900	1	2495	2019-07-01 19:30:19.347854	\N	\N	100
state:Minsk:625142	S2	Minsk	state	country:Belarus:630336	1	9499	2019-06-30 17:31:17.734541	\N	\N	100
state:Gomel:628281	S2	Gomel	state	country:Belarus:630336	1	8510	2019-06-30 17:32:09.446576	\N	\N	100
state:AlKufrah:88932	S2	Al Kufrah	state	country:Libya:2215636	1	43713	2019-06-30 18:00:46.25387	\N	\N	100
state:SulawesiSelatan:1626232	S2	Sulawesi Selatan	state	country:Indonesia:1643084	1	6230	2019-07-01 12:46:55.082706	\N	\N	100
state:Narino:3674021	S2	Nariño	state	country:Colombia:3686110	1	3325	2019-07-02 08:25:09.863958	\N	\N	100
gulf:GulfOfOman:145945	S2	Gulf of Oman	gulf	root	1	13255	2019-06-30 15:45:05.909449	\N	\N	100
state:DnipropetrovsK:709930	S2	Dnipropetrovs'k	state	country:Ukraine:690791	1	7164	2019-07-01 22:36:57.217334	\N	\N	100
state:Huila:3348303	S2	Huíla	state	country:Angola:3351879	1	7257	2019-07-02 18:08:06.343361	\N	\N	100
country:Somaliland:223816	S2	Somaliland	country	continent:Africa:6255146	0	20679	2019-06-30 20:11:41.169705	\N	\N	100
state:Kumamoto:1858419	S2	Kumamoto	state	region:Kyushu:1857892	1	531	2019-07-01 12:29:41.043679	\N	\N	100
state:Lecce:3174952	S2	Lecce	state	region:Apulia:3169778	1	2049	2019-07-01 18:31:59.417551	\N	\N	100
state:MashonalandWest:886841	S2	Mashonaland West	state	country:Zimbabwe:878675	1	9593	2019-07-01 16:59:48.900231	\N	\N	100
country:Zimbabwe:878675	S2	Zimbabwe	country	continent:Africa:6255146	0	42635	2019-07-01 16:59:48.901245	\N	\N	100
state:EastRidingOfYorkshire:2650345	S2	East Riding of Yorkshire	state	region:YorkshireAndTheHumber:11591951	1	1018	2019-07-01 19:50:24.945318	\N	\N	100
state:SouthKarelia:830699	S2	South Karelia	state	country:Finland:660013	1	3196	2019-06-30 10:10:36.306262	\N	\N	100
state:Lincolnshire:2644486	S2	Lincolnshire	state	region:EastMidlands:11591952	1	1615	2019-07-01 19:38:47.782496	\N	\N	100
region:Apulia:3169778	S2	Apulia	region	country:Italy:3175395	0	4304	2019-07-01 18:31:59.418038	\N	\N	100
state:SouthernSavonia:830695	S2	Southern Savonia	state	country:Finland:660013	1	4532	2019-06-30 10:14:15.673286	\N	\N	100
strait:BassStrait:2194168	S2	Bass Strait	strait	root	1	19035	2019-06-30 10:32:26.586998	\N	\N	100
gulf:GreatAustralianBight:2078067	S2	Great Australian Bight	gulf	root	1	75670	2019-06-30 10:32:26.014852	\N	\N	100
day:18	S2	18	day	root	1	955359	2019-07-18 04:39:56.515605	\N	\N	100
state:Ouaka:236887	S2	Ouaka	state	country:CentralAfricanRepublic:239880	1	6204	2019-07-02 16:13:18.407541	\N	\N	100
region:Sicily:2523119	S2	Sicily	region	country:Italy:3175395	0	5974	2019-06-30 18:07:11.109804	\N	\N	100
state:Catania:2525065	S2	Catania	state	region:Sicily:2523119	1	1040	2019-07-02 17:58:11.450433	\N	\N	100
state:Chardzhou:1219651	S2	Chardzhou	state	country:Turkmenistan:1218197	1	15162	2019-06-30 14:44:11.79344	\N	\N	100
sea:RedSea:408646	S2	Red Sea	sea	root	1	55152	2019-06-30 19:06:03.420205	\N	\N	100
state:HwanghaeBukto:1876888	S2	Hwanghae-bukto	state	country:NorthKorea:1873107	1	4617	2019-06-30 11:37:06.357252	\N	\N	100
state:Djelfa:2500013	S2	Djelfa	state	country:Algeria:2589581	1	5237	2019-06-30 09:51:53.434881	\N	\N	100
state:HwanghaeNamdo:1876884	S2	Hwanghae-namdo	state	country:NorthKorea:1873107	1	2555	2019-06-30 11:24:07.558191	\N	\N	100
sea:BoholSea:1724393	S2	Bohol Sea	sea	root	1	5369	2019-06-30 14:08:21.222734	\N	\N	100
state:Belgorod:578071	S2	Belgorod	state	region:Central:11961322	1	5737	2019-07-01 22:35:41.835557	\N	\N	100
state:Kagoshima:1860825	S2	Kagoshima	state	region:Kyushu:1857892	1	1017	2019-07-01 12:25:20.639146	\N	\N	100
state:Tiaret:2476893	S2	Tiaret	state	country:Algeria:2589581	1	3628	2019-07-02 19:15:29.691666	\N	\N	100
region:DakunuPalata:	S2	Dakuṇu paḷāta	region	country:SriLanka:1227603	0	1546	2019-06-30 15:18:53.222875	\N	\N	100
state:Baluchistan:1183606	S2	Baluchistan	state	country:Pakistan:1168579	1	36940	2019-06-30 14:47:51.691509	\N	\N	100
state:Lugo:3117813	S2	Lugo	state	region:Galicia:3336902	1	2019	2019-07-02 19:30:15.606555	\N	\N	100
region:Galicia:3336902	S2	Galicia	region	country:Spain:2510769	0	5894	2019-07-02 19:29:23.244446	\N	\N	100
state:Volyn:689064	S2	Volyn	state	country:Ukraine:690791	1	5094	2019-07-01 18:51:41.671792	\N	\N	100
state:RazaviKhorasan:6201375	S2	Razavi Khorasan	state	country:Iran:130758	1	17267	2019-06-30 14:46:01.660072	\N	\N	100
day:20	S2	20	day	root	1	959483	2019-07-20 09:32:40.85985	\N	\N	100
state:Central:2133763	S2	Central	state	country:PapuaNewGuinea:2088628	1	3980	2019-07-01 09:54:30.938362	\N	\N	100
state:Vasterbotten:2664415	S2	Västerbotten	state	country:Sweden:2661886	1	13674	2019-06-30 09:52:06.119436	\N	\N	100
state:Nisavski:7581801	S2	Nišavski	state	country:Serbia:6290252	1	2022	2019-07-01 18:31:03.028341	\N	\N	100
state:Jablanicki:7581795	S2	Jablanicki	state	region:Jablanicki:7581795	1	1012	2019-07-01 18:31:03.031273	\N	\N	100
region:Jablanicki:7581795	S2	Jablanički	region	country:Serbia:6290252	0	1012	2019-07-01 18:31:03.031729	\N	\N	100
state:AgusanDelSur:1731818	S2	Agusan del Sur	state	region:DinagatIslands(RegionXiii):	1	1047	2019-07-01 10:30:01.716929	\N	\N	100
state:Agadez:2448083	S2	Agadez	state	country:Niger:2440476	1	63594	2019-06-30 16:36:17.050483	\N	\N	100
state:Para:3393129	S2	Pará	state	country:Brazil:3469034	1	103626	2019-06-30 10:10:23.017899	\N	\N	100
state:Skane:3337385	S2	Skåne	state	country:Sweden:2661886	1	4586	2019-06-30 09:52:01.86381	\N	\N	100
state:NorthernAreas:1168878	S2	Northern Areas	state	country:Pakistan:1168579	1	8902	2019-06-30 16:19:35.794449	\N	\N	100
state:NewIreland:2089693	S2	New Ireland	state	country:PapuaNewGuinea:2088628	1	1030	2019-06-30 10:31:48.399588	\N	\N	100
country:Niger:2440476	S2	Niger	country	continent:Africa:6255146	0	118047	2019-06-30 16:36:17.050992	\N	\N	100
state:Muchinga:900601	S2	Muchinga	state	country:Zambia:895949	1	10770	2019-07-01 17:00:03.912214	\N	\N	100
state:Saga:1853299	S2	Saga	state	country:Japan:1861060	1	1795	2019-07-01 03:52:06.205918	\N	\N	100
state:SantaCruz:3836350	S2	Santa Cruz	state	country:Argentina:3865483	1	41391	2019-06-30 10:14:20.000565	\N	\N	100
sea:SavuSea:1818189	S2	Savu Sea	sea	root	1	13145	2019-06-30 10:33:36.907872	\N	\N	100
state:Borkou:7602866	S2	Borkou	state	country:Chad:2434508	1	29831	2019-06-30 16:54:44.758476	\N	\N	100
state:KhantyMansiy:	S2	Khanty-Mansiy	state	region:Urals:466003	1	106114	2019-06-30 15:16:51.11942	\N	\N	100
state:Nagasaki:1856156	S2	Nagasaki	state	country:Japan:1861060	1	704	2019-07-01 03:54:52.699887	\N	\N	100
state:AisenDelGeneralCarlosIbanezDelCampo:3900333	S2	Aisén del General Carlos Ibáñez del Campo	state	country:Chile:3895114	1	18659	2019-07-01 01:35:14.272946	\N	\N	100
country:Ukraine:690791	S2	Ukraine	country	continent:Europe:6255148	0	91269	2019-06-30 17:32:47.50386	\N	\N	100
state:Mykolayiv:700569	S2	Mykolayiv	state	country:Ukraine:690791	1	5643	2019-07-01 22:28:24.715025	\N	\N	100
ocean:SouthAtlanticOcean:3358844	S2	South Atlantic Ocean	ocean	root	1	549247	2019-06-30 09:52:31.137768	\N	\N	100
day:24	S2	24	day	root	1	935112	2019-07-24 03:51:01.003631	\N	\N	100
state:Troms:3133897	S2	Troms	state	country:Norway:3144096	1	8987	2019-06-30 09:52:07.100459	\N	\N	100
country:Brazil:3469034	S2	Brazil	country	continent:SouthAmerica:6255150	0	738164	2019-06-30 09:52:32.412914	\N	\N	100
country:Iceland:2629691	S2	Iceland	country	continent:Europe:6255148	0	26931	2019-06-30 21:32:08.567784	\N	\N	100
month:10	S2	10	month	root	1	2174133	2019-08-06 11:12:03.773802	\N	\N	100
region:Northern:408667	S2	Northern	region	country:Sudan:366755	0	57516	2019-06-30 16:09:34.361118	\N	\N	100
month:01	S2	01	month	root	1	1807240	2019-09-27 14:12:06.286163	\N	\N	100
state:Kirov:548389	S2	Kirov	state	region:Volga:11961325	1	25197	2019-06-30 17:00:39.973164	\N	\N	100
channel:EnglishChannel:5948867	S2	English Channel	channel	root	1	20384	2019-07-01 19:13:33.488287	\N	\N	100
state:Ostfold:3143188	S2	Østfold	state	country:Norway:3144096	1	3887	2019-06-30 21:05:15.056215	\N	\N	100
ocean:NorthAtlanticOcean:3411923	S2	North Atlantic Ocean	ocean	root	1	848045	2019-06-30 09:52:20.171523	\N	\N	100
state:Akershus:3163480	S2	Akershus	state	country:Norway:3144096	1	3816	2019-06-30 21:05:15.056641	\N	\N	100
state:AshShati:2219413	S2	Ash Shati'	state	country:Libya:2215636	1	12974	2019-06-30 17:40:56.292998	\N	\N	100
country:Sudan:366755	S2	Sudan	country	continent:Africa:6255146	0	185816	2019-06-30 16:05:43.443292	\N	\N	100
bay:PorpoiseBay:6111237	S2	Porpoise Bay	bay	root	1	951	2019-08-31 03:07:15.650841	\N	\N	100
state:Mizdah:2214827	S2	Mizdah	state	country:Libya:2215636	1	11416	2019-07-02 17:55:40.441079	\N	\N	100
state:Pituffik:3831224	S2	Pituffik	state	country:Greenland:3425505	1	5908	2019-06-30 10:15:43.011651	\N	\N	100
state:BacsKiskun:3055744	S2	Bács-Kiskun	state	region:GreatSouthernPlain:	1	1040	2019-07-02 17:56:14.499831	\N	\N	100
state:Ahal:162181	S2	Ahal	state	country:Turkmenistan:1218197	1	15302	2019-06-30 14:46:01.658786	\N	\N	100
country:Libya:2215636	S2	Libya	country	continent:Africa:6255146	0	172986	2019-06-30 17:39:53.146666	\N	\N	100
region:GreatSouthernPlain:	S2	Great Southern Plain	region	country:Hungary:719819	0	3104	2019-07-01 18:53:33.293864	\N	\N	100
state:Tolna:3043845	S2	Tolna	state	region:SouthernTransdanubia:	1	2062	2019-07-02 17:56:14.500894	\N	\N	100
region:SouthernTransdanubia:	S2	Southern Transdanubia	region	country:Hungary:719819	0	4146	2019-07-02 17:48:57.466959	\N	\N	100
state:Varmland:2664870	S2	Värmland	state	country:Sweden:2661886	1	7882	2019-07-02 01:39:13.106715	\N	\N	100
country:Hungary:719819	S2	Hungary	country	continent:Europe:6255148	0	16000	2019-06-30 18:19:06.361697	\N	\N	100
state:SevernoBacki:7581808	S2	Severno-Backi	state	region:SevernoBacki:	1	1029	2019-07-04 16:14:33.457854	\N	\N	100
region:SevernoBacki:	S2	Severno-Bački	region	country:Serbia:6290252	0	1029	2019-07-04 16:14:33.458403	\N	\N	100
state:Hedmark:3153403	S2	Hedmark	state	country:Norway:3144096	1	12636	2019-06-30 09:52:01.001919	\N	\N	100
day:11	S2	11	day	root	1	935054	2019-07-11 09:33:49.796431	\N	\N	100
state:SouthKhorasan:6201374	S2	South Khorasan	state	country:Iran:130758	1	11823	2019-06-30 14:46:05.942222	\N	\N	100
state:Silesian:3337497	S2	Silesian	state	country:Poland:798544	1	4644	2019-06-30 18:18:31.214602	\N	\N	100
state:ValleeDuBandama:2597321	S2	Vallée du Bandama	state	country:IvoryCoast:2287781	1	5235	2019-06-30 09:51:44.47107	\N	\N	100
region:Central:8335421	S2	Central	region	country:India:6269134	0	114290	2019-06-30 15:21:25.474732	\N	\N	100
state:UttarPradesh:1253626	S2	Uttar Pradesh	state	region:Central:8335421	1	23411	2019-06-30 15:21:53.283092	\N	\N	100
state:Warwickshire:2634723	S2	Warwickshire	state	region:WestMidlands:11591953	1	2022	2019-07-01 19:38:47.781592	\N	\N	100
state:Vologda:472454	S2	Vologda	state	region:Northwestern:11961321	1	36550	2019-06-30 16:58:19.622319	\N	\N	100
state:Oxfordshire:2640726	S2	Oxfordshire	state	region:SouthEast:2637438	1	1613	2019-07-01 19:41:29.390078	\N	\N	100
state:Dalarna:2699767	S2	Dalarna	state	country:Sweden:2661886	1	9868	2019-06-30 09:52:01.004367	\N	\N	100
country:IvoryCoast:2287781	S2	Ivory Coast	country	continent:Africa:6255146	0	30937	2019-06-30 09:51:43.702313	\N	\N	100
region:SouthEast:2637438	S2	South East	region	country:UnitedKingdom:2635167	0	4573	2019-07-01 19:13:33.489435	\N	\N	100
state:Rapti:1282865	S2	Rapti	state	region:MidWestern:7289706	1	1550	2019-06-30 15:37:43.62932	\N	\N	100
state:Savanes:2597320	S2	Savanes	state	country:IvoryCoast:2287781	1	5204	2019-06-30 09:51:46.288333	\N	\N	100
country:Morocco:2542007	S2	Morocco	country	continent:Africa:6255146	0	68002	2019-06-30 21:25:34.365207	\N	\N	100
state:GuelmimEsSemara:2597551	S2	Guelmim - Es-Semara	state	country:Morocco:2542007	1	12568	2019-07-01 20:51:18.05664	\N	\N	100
region:MidWestern:7289706	S2	Mid-Western	region	country:Nepal:1282988	0	5600	2019-06-30 15:37:43.629737	\N	\N	100
country:Norway:3144096	S2	Norway	country	continent:Europe:6255148	0	139847	2019-06-30 09:52:01.003176	\N	\N	100
state:Northamptonshire:2641429	S2	Northamptonshire	state	region:EastMidlands:11591952	1	1012	2019-07-01 19:41:29.388295	\N	\N	100
region:EastMidlands:11591952	S2	East Midlands	region	country:UnitedKingdom:2635167	0	3121	2019-07-01 19:38:47.782926	\N	\N	100
state:Chaco:3861887	S2	Chaco	state	country:Argentina:3865483	1	9586	2019-06-30 10:10:30.426306	\N	\N	100
state:Thuringen:2822542	S2	Thüringen	state	country:Germany:2921044	1	3149	2019-06-30 09:51:59.314484	\N	\N	100
state:Batha:2435899	S2	Batha	state	country:Chad:2434508	1	10433	2019-06-30 16:55:01.868385	\N	\N	100
country:Chad:2434508	S2	Chad	country	continent:Africa:6255146	0	119181	2019-06-30 16:54:44.759046	\N	\N	100
state:SouthCotabato:1685731	S2	South Cotabato	state	region:Soccsksargen(RegionXii):	1	522	2019-07-01 10:35:19.148491	\N	\N	100
day:12	S2	12	day	root	1	922020	2019-07-12 03:22:54.596389	\N	\N	100
state:HaIl:106280	S2	Ha'il	state	country:SaudiArabia:102358	1	14225	2019-07-01 16:10:02.865419	\N	\N	100
country:SouthAfrica:953987	S2	South Africa	country	continent:Africa:6255146	0	136838	2019-06-30 20:09:00.099042	\N	\N	100
state:Murzuq:2214602	S2	Murzuq	state	country:Libya:2215636	1	37168	2019-06-30 18:06:36.276365	\N	\N	100
state:Mary:1218666	S2	Mary	state	country:Turkmenistan:1218197	1	12714	2019-06-30 14:43:46.629571	\N	\N	100
country:Belarus:630336	S2	Belarus	country	continent:Europe:6255148	0	41269	2019-06-30 17:30:59.593282	\N	\N	100
state:Goias:3462372	S2	Goiás	state	country:Brazil:3469034	1	37993	2019-06-30 09:52:40.236647	\N	\N	100
state:Vitebsk:620134	S2	Vitebsk	state	country:Belarus:630336	1	7621	2019-06-30 17:31:17.735058	\N	\N	100
state:NorthernCape:1085596	S2	Northern Cape	state	country:SouthAfrica:953987	1	41539	2019-06-30 20:09:25.729111	\N	\N	100
country:SaudiArabia:102358	S2	Saudi Arabia	country	continent:Asia:6255147	0	197824	2019-06-30 19:07:02.179285	\N	\N	100
state:Isparta:311071	S2	Isparta	state	country:Turkey:298795	1	3611	2019-07-01 21:33:11.577432	\N	\N	100
sea:CaspianSea:630671	S2	Caspian Sea	sea	root	1	51917	2019-06-30 19:49:42.08589	\N	\N	100
state:Imo:2337542	S2	Imo	state	country:Nigeria:2328926	1	3063	2019-07-02 18:31:35.181855	\N	\N	100
state:Dhofar:285879	S2	Dhofar	state	country:Oman:286963	1	15503	2019-06-30 15:46:26.387762	\N	\N	100
state:Afyonkarahisar:325302	S2	Afyonkarahisar	state	country:Turkey:298795	1	5671	2019-07-01 21:09:42.963838	\N	\N	100
state:Usak:298298	S2	Usak	state	country:Turkey:298795	1	1550	2019-07-04 17:23:20.603572	\N	\N	100
country:Turkey:298795	S2	Turkey	country	continent:Asia:6255147	0	104459	2019-06-30 18:02:03.635688	\N	\N	100
fjord:Storfjorden:777396	S2	Storfjorden	fjord	root	1	7769	2019-06-30 09:52:10.559895	\N	\N	100
state:Svalbard:	S2	Svalbard	state	country:Norway:3144096	1	51299	2019-06-30 09:52:10.096798	\N	\N	100
state:SurigaoDelSur:1685214	S2	Surigao del Sur	state	region:DinagatIslands(RegionXiii):	1	1692	2019-07-01 10:36:40.925315	\N	\N	100
state:Siauliai:864481	S2	Šiauliai	state	country:Lithuania:597427	1	3099	2019-07-02 17:53:10.454066	\N	\N	100
state:Jelgava:459281	S2	Jelgava	state	region:Zemgale:7639660	1	2048	2019-07-02 18:08:21.555726	\N	\N	100
state:Tukums:454768	S2	Tukums	state	region:Riga:456173	1	2052	2019-07-02 18:08:21.556727	\N	\N	100
country:Lithuania:597427	S2	Lithuania	country	continent:Europe:6255148	0	13348	2019-06-30 18:17:43.754466	\N	\N	100
region:Zemgale:7639660	S2	Zemgale	region	country:Latvia:458258	0	3565	2019-07-01 18:56:22.82482	\N	\N	100
state:Brocenu:7628309	S2	Brocenu	state	region:Kurzeme:460496	1	1025	2019-07-02 18:09:31.678892	\N	\N	100
country:Latvia:458258	S2	Latvia	country	continent:Europe:6255148	0	14215	2019-06-30 10:10:32.190634	\N	\N	100
state:Aqtobe:610688	S2	Aqtöbe	state	country:Kazakhstan:1522867	1	52488	2019-06-30 15:04:54.178852	\N	\N	100
state:Ghardaia:2496045	S2	Ghardaïa	state	country:Algeria:2589581	1	10703	2019-06-30 09:51:52.879711	\N	\N	100
state:Maine:4971068	S2	Maine	state	region:Northeast:11887749	1	14087	2019-06-30 10:15:23.718788	\N	\N	100
bay:UdaBay:2014612	S2	Uda Bay	bay	root	1	5739	2019-07-01 03:52:58.768132	\N	\N	100
state:Khabarovsk:2022888	S2	Khabarovsk	state	region:FarEastern:11961349	1	136145	2019-06-30 10:14:00.085496	\N	\N	100
gulf:GulfOfMaine:4971067	S2	Gulf of Maine	gulf	root	1	11176	2019-06-30 10:15:19.38566	\N	\N	100
region:Northeast:11887749	S2	Northeast	region	country:UnitedStatesOfAmerica:6252001	0	64140	2019-06-30 10:15:19.533336	\N	\N	100
state:Lisboa:2267056	S2	Lisboa	state	region:Lisbon:2267056	1	1929	2019-07-02 19:24:07.389005	\N	\N	100
state:AlMahrah:78985	S2	Al Mahrah	state	country:Yemen:69543	1	6250	2019-07-01 19:43:53.220974	\N	\N	100
state:Hadramawt:75411	S2	Hadramawt	state	country:Yemen:69543	1	21160	2019-07-01 19:22:44.665922	\N	\N	100
state:Leiria:2267094	S2	Leiria	state	region:Centro:11886822	1	1594	2019-07-02 19:24:07.390974	\N	\N	100
country:Yemen:69543	S2	Yemen	country	continent:Asia:6255147	0	45880	2019-06-30 19:19:45.93036	\N	\N	100
region:Lisbon:2267056	S2	Lisbon	region	country:Portugal:2264397	0	1951	2019-07-02 19:24:07.389595	\N	\N	100
state:Salamanca:3111107	S2	Salamanca	state	region:CastillaYLeon:3336900	1	4548	2019-07-01 19:13:48.788406	\N	\N	100
state:Saratov:498671	S2	Saratov	state	region:Volga:11961325	1	17178	2019-06-30 19:06:41.453226	\N	\N	100
state:Dornod:2031799	S2	Dornod	state	country:Mongolia:2029969	1	22703	2019-06-30 12:35:08.963133	\N	\N	100
state:Lodz:3337493	S2	Łódź	state	country:Poland:798544	1	6707	2019-06-30 17:58:15.39255	\N	\N	100
state:Swietokrzyskie:769251	S2	Świętokrzyskie	state	country:Poland:798544	1	3110	2019-07-02 17:48:56.622933	\N	\N	100
region:CastillaYLeon:3336900	S2	Castilla y León	region	country:Spain:2510769	0	17609	2019-07-01 19:13:48.788868	\N	\N	100
state:Rivne:695592	S2	Rivne	state	country:Ukraine:690791	1	3574	2019-06-30 19:02:59.299075	\N	\N	100
state:Imereti:865539	S2	Imereti	state	country:Georgia:614540	1	2005	2019-07-02 17:18:01.872474	\N	\N	100
state:Guria:865538	S2	Guria	state	country:Georgia:614540	1	2018	2019-07-02 17:22:52.393551	\N	\N	100
country:Romania:798549	S2	Romania	country	continent:Europe:6255148	0	36790	2019-06-30 18:09:25.10035	\N	\N	100
state:Ajaria:615929	S2	Ajaria	state	country:Georgia:614540	1	2035	2019-07-02 17:22:52.394713	\N	\N	100
country:Georgia:614540	S2	Georgia	country	continent:Asia:6255147	0	11891	2019-06-30 19:03:23.983202	\N	\N	100
state:SamegreloZemoSvaneti:865543	S2	Samegrelo-Zemo Svaneti	state	country:Georgia:614540	1	3519	2019-07-02 17:03:04.664688	\N	\N	100
state:Hunedoara:675917	S2	Hunedoara	state	country:Romania:798549	1	2032	2019-07-01 19:06:20.154398	\N	\N	100
state:Cluj:681291	S2	Cluj	state	country:Romania:798549	1	3049	2019-07-01 18:52:55.687084	\N	\N	100
state:SognOgFjordane:3137966	S2	Sogn og Fjordane	state	country:Norway:3144096	1	7417	2019-06-30 21:05:22.898326	\N	\N	100
state:Alba:686581	S2	Alba	state	country:Romania:798549	1	3533	2019-07-01 18:52:55.686405	\N	\N	100
state:LaayouneBoujdourSakiaElHamra:6545402	S2	Laâyoune - Boujdour - Sakia El Hamra	state	country:Morocco:2542007	1	8691	2019-07-02 20:09:50.847127	\N	\N	100
channel:CanalPerigoso:3392287	S2	Canal Perigoso	channel	root	1	854	2019-07-03 20:00:41.7146	\N	\N	100
state:Georgia:4197000	S2	Georgia	state	region:South:11887752	1	18733	2019-06-30 10:16:21.789987	\N	\N	100
state:SulawesiTengah:1626231	S2	Sulawesi Tengah	state	country:Indonesia:1643084	1	7536	2019-06-30 13:47:01.328842	\N	\N	100
state:NordrheinWestfalen:2861876	S2	Nordrhein-Westfalen	state	country:Germany:2921044	1	9841	2019-06-30 09:51:55.942362	\N	\N	100
state:Maramures:673887	S2	Maramures	state	country:Romania:798549	1	4061	2019-07-01 19:00:46.885669	\N	\N	100
state:Eastern:917388	S2	Eastern	state	country:Zambia:895949	1	7133	2019-07-01 17:06:14.860314	\N	\N	100
state:Denizli:317106	S2	Denizli	state	country:Turkey:298795	1	2088	2019-07-04 17:22:46.041517	\N	\N	100
state:IvanoFrankivsK:707471	S2	Ivano-Frankivs'k	state	country:Ukraine:690791	1	4577	2019-07-01 18:34:17.143589	\N	\N	100
month:08	S2	08	month	root	1	2826950	2019-08-01 04:23:25.089405	\N	\N	100
state:Cordoba:3860255	S2	Córdoba	state	country:Argentina:3865483	1	20313	2019-06-30 10:10:27.596187	\N	\N	100
country:SouthSudan:7909807	S2	South Sudan	country	continent:Africa:6255146	0	57642	2019-06-30 16:06:19.926265	\N	\N	100
state:SantaFe:3836276	S2	Santa Fe	state	country:Argentina:3865483	1	14784	2019-06-30 10:10:25.643365	\N	\N	100
state:Maranhao:3395443	S2	Maranhão	state	country:Brazil:3469034	1	29804	2019-06-30 09:52:49.629861	\N	\N	100
state:Navoi:1513131	S2	Navoi	state	country:Uzbekistan:1512440	1	18468	2019-06-30 14:42:18.316013	\N	\N	100
state:SulawesiUtara:1626229	S2	Sulawesi Utara	state	country:Indonesia:1643084	1	2539	2019-07-01 12:22:01.801621	\N	\N	100
state:Tamanghasset:2478217	S2	Tamanghasset	state	country:Algeria:2589581	1	67002	2019-06-30 09:51:51.641716	\N	\N	100
state:Samarkand:1114927	S2	Samarkand	state	country:Uzbekistan:1512440	1	6059	2019-07-02 14:01:01.877099	\N	\N	100
state:Erzurum:315367	S2	Erzurum	state	country:Turkey:298795	1	6090	2019-07-02 17:03:23.789785	\N	\N	100
gulf:GulfOfTomini:6999447	S2	Gulf of Tomini	gulf	root	1	6537	2019-06-30 13:49:21.106294	\N	\N	100
country:Uzbekistan:1512440	S2	Uzbekistan	country	continent:Asia:6255147	0	68663	2019-06-30 14:42:18.316485	\N	\N	100
state:Komi:545854	S2	Komi	state	region:Northwestern:11961321	1	91417	2019-06-30 16:57:41.817587	\N	\N	100
state:Antarctica:6632699	S2	Antarctica	state	country:Antarctica:6632699	1	676323	2019-08-25 09:50:48.024147	\N	\N	100
state:NorthKazakhstan:1519367	S2	North Kazakhstan	state	country:Kazakhstan:1522867	1	21469	2019-06-30 14:42:39.843731	\N	\N	100
state:PrimorYe:505251	S2	Primor'ye	state	region:FarEastern:11961349	1	23230	2019-07-01 03:38:51.442481	\N	\N	100
country:Antarctica:6632699	S2	Antarctica	country	continent:Antarctica:	0	679050	2019-08-25 09:50:48.024913	\N	\N	100
country:AmericanSamoa:5880801	S2	American Samoa	country	continent:Oceania:6255151	0	1475	2019-06-30 10:31:50.456347	\N	\N	100
state:Sudurland:3337406	S2	Suðurland	state	country:Iceland:2629691	1	7009	2019-07-01 20:40:23.851354	\N	\N	100
state:Western:5880873	S2	Western	state	country:AmericanSamoa:5880801	1	980	2019-06-30 10:31:50.455643	\N	\N	100
state:MatoGrossoDoSul:3457415	S2	Mato Grosso do Sul	state	country:Brazil:3469034	1	38665	2019-06-30 22:27:27.551516	\N	\N	100
state:Yamaguchi:1848681	S2	Yamaguchi	state	region:Chugoku:1864492	1	1578	2019-07-01 11:56:08.200408	\N	\N	100
region:Chugoku:1864492	S2	Chugoku	region	country:Japan:1861060	0	5791	2019-07-01 11:56:08.200941	\N	\N	100
state:AlJufrah:2219944	S2	Al Jufrah	state	country:Libya:2215636	1	12630	2019-07-01 18:27:18.538381	\N	\N	100
sea:VisayanSea:1679787	S2	Visayan Sea	sea	root	1	2581	2019-06-30 14:08:21.222106	\N	\N	100
sea:SamarSea:1690647	S2	Samar Sea	sea	root	1	2602	2019-06-30 14:10:11.503058	\N	\N	100
state:HauteKotto:238640	S2	Haute-Kotto	state	country:CentralAfricanRepublic:239880	1	9241	2019-07-01 21:48:46.722901	\N	\N	100
region:EasternVisayas(RegionViii):	S2	Eastern Visayas (Region VIII)	region	country:Philippines:1694008	0	3158	2019-06-30 14:12:27.791965	\N	\N	100
state:Evora:2268404	S2	Évora	state	region:Alentejo:2268252	1	1027	2019-07-04 18:22:24.658181	\N	\N	100
region:Alentejo:2268252	S2	Alentejo	region	country:Portugal:2264397	0	3698	2019-07-01 19:36:55.065691	\N	\N	100
state:Setubal:2262961	S2	Setúbal	state	region:Lisbon:2267056	1	521	2019-07-04 18:22:24.659457	\N	\N	100
state:Leyte:1706800	S2	Leyte	state	region:EasternVisayas(RegionViii):	1	1053	2019-07-01 10:31:21.101663	\N	\N	100
year:2020	S2	2020	year	root	1	5752324	2020-01-01 02:38:13.734098	\N	\N	100
state:Orebro:2686655	S2	Orebro	state	country:Sweden:2661886	1	3650	2019-07-02 01:39:13.10594	\N	\N	100
state:VastraGotaland:3337386	S2	Västra Götaland	state	country:Sweden:2661886	1	8925	2019-06-30 09:52:02.38329	\N	\N	100
region:Central:11961322	S2	Central	region	country:Russia:2017370	0	115987	2019-06-30 16:58:19.623742	\N	\N	100
state:Kaluga:553899	S2	Kaluga	state	region:Central:11961322	1	4141	2019-07-01 19:41:27.770466	\N	\N	100
state:Penza:511555	S2	Penza	state	region:Volga:11961325	1	9792	2019-06-30 19:01:52.720289	\N	\N	100
state:Adrar:2381972	S2	Adrar	state	country:Mauritania:2378080	1	24438	2019-07-01 20:52:47.524885	\N	\N	100
state:TirisZemmour:2375989	S2	Tiris Zemmour	state	country:Mauritania:2378080	1	33416	2019-06-30 22:24:12.855201	\N	\N	100
state:Inchiri:2378903	S2	Inchiri	state	country:Mauritania:2378080	1	5381	2019-07-02 20:10:37.470966	\N	\N	100
country:Mauritania:2378080	S2	Mauritania	country	continent:Africa:6255146	0	104863	2019-06-30 21:25:58.3964	\N	\N	100
state:AinDefla:2508226	S2	Aïn Defla	state	country:Algeria:2589581	1	3056	2019-06-30 09:51:52.723234	\N	\N	100
gulf:DavaoGulf:1715344	S2	Davao Gulf	gulf	root	1	2591	2019-07-01 10:39:54.393198	\N	\N	100
state:DavaoDelSur:1715345	S2	Davao del Sur	state	region:Davao(RegionXi):	1	1028	2019-07-01 10:34:36.194159	\N	\N	100
region:Davao(RegionXi):	S2	Davao (Region XI)	region	country:Philippines:1694008	0	3160	2019-07-01 10:38:03.58294	\N	\N	100
state:Cotabato:1697559	S2	Cotabato	state	region:Soccsksargen(RegionXii):	1	1529	2019-07-01 10:38:03.581919	\N	\N	100
region:Soccsksargen(RegionXii):	S2	SOCCSKSARGEN (Region XII)	region	country:Philippines:1694008	0	2577	2019-07-01 10:34:36.195166	\N	\N	100
gulf:GolfeDuLion:2960849	S2	Golfe du Lion	gulf	root	1	2632	2019-07-02 19:16:31.676754	\N	\N	100
state:Karelia:552548	S2	Karelia	state	region:Northwestern:11961321	1	39833	2019-06-30 10:10:20.557819	\N	\N	100
state:Fukuoka:1863958	S2	Fukuoka	state	region:Kyushu:1857892	1	541	2019-07-01 12:40:02.865295	\N	\N	100
state:SaoPaulo:3448433	S2	São Paulo	state	country:Brazil:3469034	1	28820	2019-06-30 09:52:37.98942	\N	\N	100
state:Zanzan:2597325	S2	Zanzan	state	country:IvoryCoast:2287781	1	2320	2019-06-30 09:51:45.217958	\N	\N	100
state:NorthDarfur:408666	S2	North Darfur	state	region:Darfur:408660	1	30837	2019-07-01 21:24:29.467793	\N	\N	100
region:Darfur:408660	S2	Darfur	region	country:Sudan:366755	0	52451	2019-07-01 21:24:29.468302	\N	\N	100
region:Volga:11961325	S2	Volga	region	country:Russia:2017370	0	271291	2019-06-30 17:00:39.973654	\N	\N	100
sea:LabradorSea:5994848	S2	Labrador Sea	sea	root	1	69304	2019-06-30 10:10:23.048224	\N	\N	100
strait:DenmarkStrait:3424930	S2	Denmark Strait	strait	root	1	15025	2019-06-30 09:52:25.054077	\N	\N	100
state:Parana:3455077	S2	Paraná	state	country:Brazil:3469034	1	21968	2019-06-30 09:52:34.865577	\N	\N	100
state:SantaCatarina:3450387	S2	Santa Catarina	state	country:Brazil:3469034	1	9194	2019-06-30 09:52:33.261842	\N	\N	100
state:Bashkortostan:578853	S2	Bashkortostan	state	region:Volga:11961325	1	25430	2019-06-30 18:44:23.529841	\N	\N	100
season:autumn	S2	Autumn	season	root	1	5679787	2019-08-06 11:12:03.778308	\N	\N	100
sea:SeaOfJapan:2038684	S2	Sea of Japan	sea	root	1	144905	2019-06-30 10:13:56.986482	\N	\N	100
state:Leningrad:536199	S2	Leningrad	state	region:Northwestern:11961321	1	19532	2019-06-30 10:10:31.16508	\N	\N	100
state:Guam:4043988	S2	Guam	state	country:Guam:4043988	1	1438	2019-06-30 10:32:30.622304	\N	\N	100
region:DinagatIslands(RegionXiii):	S2	Dinagat Islands (Region XIII)	region	country:Philippines:1694008	0	3257	2019-07-01 10:30:01.717771	\N	\N	100
state:Matam:2246451	S2	Matam	state	country:Senegal:2245662	1	4099	2019-07-01 21:13:15.627641	\N	\N	100
country:Senegal:2245662	S2	Senegal	country	continent:Africa:6255146	0	21132	2019-07-01 20:57:52.5688	\N	\N	100
sea:CelebesSea:1818203	S2	Celebes Sea	sea	root	1	44133	2019-06-30 12:19:22.578198	\N	\N	100
state:Gorgol:2379384	S2	Gorgol	state	country:Mauritania:2378080	1	3062	2019-07-01 20:59:32.954173	\N	\N	100
state:LanaoDelNorte:1707668	S2	Lanao del Norte	state	region:NorthernMindanao(RegionX):	1	511	2019-07-01 10:43:45.64883	\N	\N	100
country:Guam:4043988	S2	Guam	country	continent:Oceania:6255151	0	1438	2019-06-30 10:32:30.623046	\N	\N	100
state:SouthTyneside:3333199	S2	South Tyneside	state	region:NorthEast:11591950	1	1512	2019-07-01 19:32:50.854636	\N	\N	100
region:NorthernMindanao(RegionX):	S2	Northern Mindanao (Region X)	region	country:Philippines:1694008	0	3098	2019-07-01 10:36:41.360308	\N	\N	100
state:AgusanDelNorte:1731819	S2	Agusan del Norte	state	region:DinagatIslands(RegionXiii):	1	507	2019-07-01 10:30:22.484087	\N	\N	100
bay:BaiaDeSaoMarcos:3388327	S2	Baía de São Marcos	bay	root	1	1012	2019-07-04 19:24:52.972919	\N	\N	100
state:SarajevoRomanija:	S2	Sarajevo-romanija	state	region:RepuplikaSrpska:	1	517	2019-07-04 16:37:42.247894	\N	\N	100
region:FederacijaBosnaIHercegovina:	S2	Federacija Bosna i Hercegovina	region	country:BosniaAndHerzegovina:3277605	0	5720	2019-07-02 17:55:10.549019	\N	\N	100
state:Leon:3118528	S2	León	state	region:CastillaYLeon:3336900	1	3066	2019-07-01 19:51:04.231966	\N	\N	100
state:HerzegovinaNeretva:3343733	S2	Herzegovina-Neretva	state	region:FederacijaBosnaIHercegovina:	1	2061	2019-07-02 17:55:35.218984	\N	\N	100
region:RepuplikaSrpska:	S2	Repuplika Srpska	region	country:BosniaAndHerzegovina:3277605	0	4679	2019-07-02 17:49:02.616538	\N	\N	100
country:BosniaAndHerzegovina:3277605	S2	Bosnia and Herzegovina	country	continent:Europe:6255148	0	7299	2019-07-02 17:49:02.616962	\N	\N	100
continent:SouthAmerica:6255150	S2	South America	continent	root	0	1719491	2019-06-30 09:52:32.413355	\N	\N	100
state:Asturias:3114710	S2	Asturias	state	region:Asturias:3114710	1	3057	2019-07-01 19:46:55.357129	\N	\N	100
region:Asturias:3114710	S2	Asturias	region	country:Spain:2510769	0	3057	2019-07-01 19:46:55.357619	\N	\N	100
continent:SevenSeas(OpenOcean):	S2	Seven seas (open ocean)	continent	root	0	51145	2019-06-30 13:42:23.179475	\N	\N	100
state:Noumbiel:2597266	S2	Noumbiel	state	region:SudOuest:6930714	1	1039	2019-06-30 09:51:47.259345	\N	\N	100
day:17	S2	17	day	root	1	950941	2019-07-17 03:26:52.335886	\N	\N	100
state:NuevoLeon:3522542	S2	Nuevo León	state	country:Mexico:3996063	1	8457	2019-06-30 10:16:09.389574	\N	\N	100
state:Ostergotland:2685867	S2	Östergötland	state	country:Sweden:2661886	1	2947	2019-07-02 01:38:15.100103	\N	\N	100
state:Moxico:875996	S2	Moxico	state	country:Angola:3351879	1	21076	2019-06-30 09:51:40.244863	\N	\N	100
state:Bie:3351640	S2	Bié	state	country:Angola:3351879	1	8814	2019-06-30 09:51:40.407286	\N	\N	100
state:Brindisi:3181526	S2	Brindisi	state	region:Apulia:3169778	1	1032	2019-07-04 16:21:02.448333	\N	\N	100
sea:AdriaticSea:3183462	S2	Adriatic Sea	sea	root	1	25610	2019-06-30 17:58:23.378451	\N	\N	100
country:Angola:3351879	S2	Angola	country	continent:Africa:6255146	0	118269	2019-06-30 09:51:38.926858	\N	\N	100
country:Tanzania:149590	S2	Tanzania	country	continent:Africa:6255146	0	90670	2019-06-30 19:55:48.798129	\N	\N	100
state:LundaNorte:145702	S2	Lunda Norte	state	country:Angola:3351879	1	11941	2019-06-30 10:10:59.760402	\N	\N	100
state:Rukwa:150442	S2	Rukwa	state	country:Tanzania:149590	1	3811	2019-07-01 17:17:56.653507	\N	\N	100
state:Malanje:2239858	S2	Malanje	state	country:Angola:3351879	1	10926	2019-06-30 09:51:40.406763	\N	\N	100
state:Adrar:2508807	S2	Adrar	state	country:Algeria:2589581	1	54037	2019-06-30 09:51:50.262801	\N	\N	100
bay:BayOfBiscay:2960858	S2	Bay of Biscay	bay	root	1	31632	2019-07-01 19:13:08.842854	\N	\N	100
state:WindwardIslands:4034365	S2	Windward Islands	state	country:FrenchPolynesia:4030656	1	992	2019-07-03 02:01:38.743883	\N	\N	100
state:Sakarya:740352	S2	Sakarya	state	country:Turkey:298795	1	2572	2019-07-04 15:11:46.879702	\N	\N	100
country:FrenchPolynesia:4030656	S2	French Polynesia	country	continent:Oceania:6255151	0	996	2019-07-03 02:01:38.744399	\N	\N	100
state:Kocaeli:742865	S2	Kocaeli	state	country:Turkey:298795	1	1041	2019-07-02 16:49:10.382469	\N	\N	100
state:AkwaIbom:2350813	S2	Akwa Ibom	state	country:Nigeria:2328926	1	511	2019-07-04 15:43:51.289406	\N	\N	100
state:Abia:2565340	S2	Abia	state	country:Nigeria:2328926	1	1527	2019-07-02 18:51:42.438765	\N	\N	100
sea:TasmanSea:2208323	S2	Tasman Sea	sea	root	1	112142	2019-06-30 10:10:22.178536	\N	\N	100
state:Northern:900601	S2	Northern	state	country:Zambia:895949	1	7574	2019-07-01 17:00:03.911529	\N	\N	100
country:Zambia:895949	S2	Zambia	country	continent:Africa:6255146	0	74925	2019-06-30 15:39:20.053306	\N	\N	100
state:Ohio:5165418	S2	Ohio	state	region:Midwest:11887750	1	18894	2019-06-30 10:16:01.020528	\N	\N	100
state:Kogi:2597364	S2	Kogi	state	country:Nigeria:2328926	1	4618	2019-07-02 18:31:15.274594	\N	\N	100
state:NorthernIslands:4041467	S2	Northern Islands	state	country:NorthernMarianaIslands:4041467	1	134	2022-06-09 04:00:27.499794	\N	\N	100
country:NorthernMarianaIslands:4041467	S2	Northern Mariana Islands	country	continent:Oceania:6255151	0	624	2019-07-02 20:52:22.631263	\N	\N	100
state:Vastmanland:2664179	S2	Västmanland	state	country:Sweden:2661886	1	2869	2019-07-02 01:39:36.792095	\N	\N	100
state:Lapland:830603	S2	Lapland	state	country:Finland:660013	1	25365	2019-06-30 09:52:08.627023	\N	\N	100
country:Sweden:2661886	S2	Sweden	country	continent:Europe:6255148	0	97841	2019-06-30 09:52:01.005562	\N	\N	100
state:Gao:2457161	S2	Gao	state	country:Mali:2453866	1	20165	2019-06-30 09:51:50.568421	\N	\N	100
country:Finland:660013	S2	Finland	country	continent:Europe:6255148	0	79090	2019-06-30 09:52:08.628364	\N	\N	100
state:Finnmark:780166	S2	Finnmark	state	country:Norway:3144096	1	19729	2019-06-30 09:52:08.413176	\N	\N	100
year:2024	S2	2024	year	root	1	1893527	2024-01-01 01:28:55.872932	\N	\N	100
state:Sakha(Yakutia):	S2	Sakha (Yakutia)	state	region:FarEastern:11961349	1	643459	2019-06-30 10:14:04.037636	\N	\N	100
state:Cunene:3349096	S2	Cunene	state	country:Angola:3351879	1	7964	2019-07-02 18:06:50.938882	\N	\N	100
strait:DavisStrait:5935734	S2	Davis Strait	strait	root	1	116914	2019-06-30 10:10:39.566365	\N	\N	100
state:ArkhangelSk:581043	S2	Arkhangel'sk	state	region:Northwestern:11961321	1	132535	2019-06-30 09:52:15.577864	\N	\N	100
region:Northwestern:11961321	S2	Northwestern	region	country:Russia:2017370	0	429352	2019-06-30 09:52:15.578572	\N	\N	100
state:QeqqataKommunia:7602006	S2	Qeqqata Kommunia	state	country:Greenland:3425505	1	25820	2019-06-30 10:10:39.2821	\N	\N	100
sea:EastSiberianSea:2127381	S2	East Siberian Sea	sea	root	1	135240	2019-06-30 10:14:03.406912	\N	\N	100
state:Miyazaki:1856710	S2	Miyazaki	state	region:Kyushu:1857892	1	517	2019-07-01 12:29:09.156309	\N	\N	100
region:Kyushu:1857892	S2	Kyushu	region	country:Japan:1861060	0	3175	2019-07-01 12:25:20.639897	\N	\N	100
state:Bayern:2951839	S2	Bayern	state	country:Germany:2921044	1	12335	2019-06-30 18:22:21.041658	\N	\N	100
state:Gavleborg:2712411	S2	Gävleborg	state	country:Sweden:2661886	1	5265	2019-07-02 01:37:12.696719	\N	\N	100
day:25	S2	25	day	root	1	951897	2019-06-30 10:10:22.172319	\N	\N	100
month:04	S2	04	month	root	1	2514446	2019-09-27 09:42:55.539004	\N	\N	100
season:spring	S2	Spring	season	root	1	8401688	2019-09-05 14:24:44.772438	\N	\N	100
gulf:GulfOfBothnia:630672	S2	Gulf of Bothnia	gulf	root	1	30421	2019-06-30 17:59:42.291587	\N	\N	100
state:Tov:2028849	S2	Töv	state	country:Mongolia:2029969	1	13933	2019-06-30 13:33:12.803934	\N	\N	100
continent:Africa:6255146	S2	Africa	continent	root	0	2818247	2019-06-30 09:51:38.329272	\N	\N	100
state:Bolivar:3648106	S2	Bolívar	state	country:Venezuela:3625428	1	24932	2019-06-30 10:10:40.237141	\N	\N	100
country:Venezuela:3625428	S2	Venezuela	country	continent:SouthAmerica:6255150	0	84980	2019-06-30 10:10:30.016007	\N	\N	100
state:Reykjavik:3413829	S2	Reykjavík	state	country:Iceland:2629691	1	813	2019-07-02 20:46:21.809756	\N	\N	100
state:Sudurnes:3426183	S2	Suðurnes	state	country:Iceland:2629691	1	2032	2019-06-30 21:37:53.48995	\N	\N	100
state:Hofudborgarsvaedi:3426182	S2	Höfuðborgarsvæði	state	country:Iceland:2629691	1	813	2019-07-02 20:46:21.811146	\N	\N	100
country:Nigeria:2328926	S2	Nigeria	country	continent:Africa:6255146	0	80865	2019-06-30 17:42:51.92091	\N	\N	100
state:Benue:2347266	S2	Benue	state	country:Nigeria:2328926	1	2705	2019-07-04 15:37:35.656264	\N	\N	100
state:Vesturland:3426184	S2	Vesturland	state	country:Iceland:2629691	1	3274	2019-06-30 21:32:08.567221	\N	\N	100
state:Murmansk:524304	S2	Murmansk	state	region:Northwestern:11961321	1	36777	2019-06-30 10:10:25.340555	\N	\N	100
state:WestNewBritain:2083546	S2	West New Britain	state	country:PapuaNewGuinea:2088628	1	3232	2019-06-30 10:31:49.077249	\N	\N	100
sea:MediterraneanSea:363196	S2	Mediterranean Sea	sea	root	1	200981	2019-06-30 09:51:53.595974	\N	\N	100
state:Timbuktu:2449066	S2	Timbuktu	state	country:Mali:2453866	1	55200	2019-06-30 09:51:49.967192	\N	\N	100
state:WadiAlHayaa:	S2	Wadi al Hayaa	state	country:Libya:2215636	1	5707	2019-07-02 17:58:05.98271	\N	\N	100
country:Mali:2453866	S2	Mali	country	continent:Africa:6255146	0	132973	2019-06-30 09:51:46.846633	\N	\N	100
state:Brandenburg:2945356	S2	Brandenburg	state	country:Germany:2921044	1	8269	2019-07-02 01:26:14.526331	\N	\N	100
state:Timis:665091	S2	Timis	state	country:Romania:798549	1	2043	2019-07-01 18:57:00.785963	\N	\N	100
state:SachsenAnhalt:2842565	S2	Sachsen-Anhalt	state	country:Germany:2921044	1	4658	2019-07-02 01:30:14.725799	\N	\N	100
region:Norte:3372843	S2	Norte	region	country:Portugal:2264397	0	3494	2019-07-01 19:31:46.515335	\N	\N	100
country:Portugal:2264397	S2	Portugal	country	continent:Europe:6255148	0	11922	2019-07-01 19:13:48.787947	\N	\N	100
state:JuznoBanatski:	S2	Južno-Banatski	state	country:Serbia:6290252	1	3067	2019-07-01 18:36:47.853638	\N	\N	100
region:Centro:11886822	S2	Centro	region	country:Portugal:2264397	0	3459	2019-07-01 19:13:48.787465	\N	\N	100
country:Serbia:6290252	S2	Serbia	country	continent:Europe:6255148	0	12465	2019-07-01 18:31:03.032238	\N	\N	100
state:Aveiro:2742610	S2	Aveiro	state	region:NorteCentro:	1	1995	2019-07-02 20:21:14.740156	\N	\N	100
region:NorteCentro:	S2	Norte, Centro	region	country:Portugal:2264397	0	1995	2019-07-02 20:21:14.740682	\N	\N	100
country:Germany:2921044	S2	Germany	country	continent:Europe:6255148	0	64022	2019-06-30 09:51:55.692129	\N	\N	100
state:SevernoBanatski:7581905	S2	Severno-Banatski	state	country:Serbia:6290252	1	1531	2019-07-01 19:09:18.875818	\N	\N	100
state:Porto:2735941	S2	Porto	state	region:Norte:3372843	1	1013	2019-07-02 20:32:43.877075	\N	\N	100
state:JuznoBacki:	S2	Južno-Backi	state	region:JuznoBacki:	1	1556	2019-07-01 19:09:18.876287	\N	\N	100
state:Viseu:2732264	S2	Viseu	state	region:Centro:11886822	1	508	2019-07-04 18:29:46.767159	\N	\N	100
region:JuznoBacki:	S2	Južno-Bački	region	country:Serbia:6290252	0	1556	2019-07-01 19:09:18.876691	\N	\N	100
state:SrednjeBanatski:7581906	S2	Srednje-Banatski	state	country:Serbia:6290252	1	1023	2019-07-01 19:09:18.877112	\N	\N	100
gulf:GolfoSanJorge:3831703	S2	Golfo San Jorge	gulf	root	1	4688	2019-06-30 10:14:51.231228	\N	\N	100
state:KalimantanTimur:1641897	S2	Kalimantan Timur	state	country:Indonesia:1643084	1	19756	2019-06-30 11:56:49.252255	\N	\N	100
state:KalimantanTengah:1641898	S2	Kalimantan Tengah	state	country:Indonesia:1643084	1	16066	2019-06-30 11:58:46.240293	\N	\N	100
state:Otago:6612109	S2	Otago	state	region:SouthIsland:2182504	1	5062	2019-06-30 10:32:07.866106	\N	\N	100
state:CentralDarfur:8394436	S2	Central Darfur	state	region:Darfur:408660	1	4990	2019-07-01 21:24:54.139821	\N	\N	100
state:Xinjiang:1529047	S2	Xinjiang	state	region:NorthwestChina:	1	205517	2019-06-30 15:19:53.494384	\N	\N	100
gulf:GulfOfGuinea:2363255	S2	Gulf of Guinea	gulf	root	1	49904	2019-06-30 09:51:43.701764	\N	\N	100
state:SudComoe:2597317	S2	Sud-Comoé	state	country:IvoryCoast:2287781	1	1569	2019-06-30 09:51:44.644276	\N	\N	100
country:Argentina:3865483	S2	Argentina	country	continent:SouthAmerica:6255150	0	324060	2019-06-30 10:10:20.366464	\N	\N	100
state:Peten:3591410	S2	Petén	state	country:Guatemala:3595528	1	5170	2019-06-30 10:15:27.671273	\N	\N	100
state:Omsk:1496152	S2	Omsk	state	region:Siberian:11961345	1	26417	2019-06-30 15:16:53.107414	\N	\N	100
sea:CaribbeanSea:4563233	S2	Caribbean Sea	sea	root	1	271505	2019-06-30 10:10:30.015154	\N	\N	100
state:HauteMarne:3013757	S2	Haute-Marne	state	region:ChampagneArdenne:11071622	1	1044	2019-06-30 09:51:55.256919	\N	\N	100
state:Central:2109495	S2	Central	state	country:SolomonIslands:2103350	1	2482	2019-07-01 08:58:21.728244	\N	\N	100
state:Vosges:2967681	S2	Vosges	state	region:Lorraine:11071622	1	2570	2019-06-30 09:51:55.254967	\N	\N	100
state:Malaita:2106552	S2	Malaita	state	country:SolomonIslands:2103350	1	1490	2019-07-03 05:04:21.30015	\N	\N	100
region:Lorraine:11071622	S2	Lorraine	region	country:France:3017382	0	5693	2019-06-30 09:51:55.255953	\N	\N	100
country:SolomonIslands:2103350	S2	Solomon Islands	country	continent:Oceania:6255151	0	3486	2019-06-30 18:43:17.838221	\N	\N	100
country:France:3017382	S2	France	country	continent:Europe:6255148	0	90046	2019-06-30 09:51:55.046086	\N	\N	100
region:ChampagneArdenne:11071622	S2	Champagne-Ardenne	region	country:France:3017382	0	5670	2019-06-30 09:51:55.257899	\N	\N	100
state:Ishikawa:1861387	S2	Ishikawa	state	region:Chubu:1864496	1	552	2019-06-30 10:33:39.433913	\N	\N	100
country:Algeria:2589581	S2	Algeria	country	continent:Africa:6255146	0	235243	2019-06-30 09:51:50.263653	\N	\N	100
state:Kidal:2597449	S2	Kidal	state	country:Mali:2453866	1	17777	2019-06-30 09:51:51.184238	\N	\N	100
state:Principe:2410878	S2	Príncipe	state	country:SaoTomeAndPrincipe:2410758	1	504	2019-07-04 16:19:49.450906	\N	\N	100
state:LaPampa:3849574	S2	La Pampa	state	country:Argentina:3865483	1	21609	2019-06-30 10:14:23.872605	\N	\N	100
state:Colima:4013513	S2	Colima	state	country:Mexico:3996063	1	1032	2019-07-03 02:34:20.508982	\N	\N	100
state:SanLuis:3837029	S2	San Luis	state	country:Argentina:3865483	1	12360	2019-06-30 10:10:20.366053	\N	\N	100
state:Bahia:3471168	S2	Bahia	state	country:Brazil:3469034	1	54366	2019-06-30 09:52:47.930276	\N	\N	100
country:Pakistan:1168579	S2	Pakistan	country	continent:Asia:6255147	0	91824	2019-06-30 14:47:51.692025	\N	\N	100
state:Punjab:1167710	S2	Punjab	state	country:Pakistan:1168579	1	24522	2019-06-30 16:16:06.445062	\N	\N	100
state:K.P.:1168873	S2	K.P.	state	country:Pakistan:1168579	1	12170	2019-06-30 16:19:10.115299	\N	\N	100
state:Piaui:3392213	S2	Piauí	state	country:Brazil:3469034	1	26134	2019-06-30 09:52:51.385174	\N	\N	100
location:equatorial	S2	Equatorial	location	root	1	109546	2019-06-30 10:10:23.017292	\N	\N	100
state:Amazonas:3665361	S2	Amazonas	state	country:Brazil:3469034	1	139287	2019-06-30 10:10:27.641849	\N	\N	100
bay:JamesBay:5985541	S2	James Bay	bay	root	1	12062	2019-06-30 10:17:13.551837	\N	\N	100
sea:BaliSea:1818207	S2	Bali Sea	sea	root	1	5932	2019-06-30 12:12:13.263809	\N	\N	100
lagoon:LakePontchartrain:4337792	S2	Lake Pontchartrain	lagoon	root	1	2023	2019-07-01 02:51:11.888331	\N	\N	100
state:Louisiana:4331987	S2	Louisiana	state	region:South:11887752	1	14983	2019-07-01 02:46:58.494163	\N	\N	100
state:Amur:2027748	S2	Amur	state	region:FarEastern:11961349	1	59287	2019-06-30 11:36:27.733256	\N	\N	100
state:Quebec:6115047	S2	Québec	state	region:EasternCanada:	1	253513	2019-06-30 10:10:30.472421	\N	\N	100
sea:GreenlandSea:2960853	S2	Greenland Sea	sea	root	1	403849	2019-06-30 09:52:08.788432	\N	\N	100
region:FarEastern:11961349	S2	Far Eastern	region	country:Russia:2017370	0	1224295	2019-06-30 10:10:41.290276	\N	\N	100
country:Russia:2017370	S2	Russia	country	continent:Europe:6255148	0	3332949	2019-06-30 09:52:15.578941	\N	\N	100
continent:Europe:6255148	S2	Europe	continent	root	0	5103818	2019-06-30 09:51:55.047025	\N	\N	100
ocean:SouthPacificOcean:4030483	S2	South Pacific Ocean	ocean	root	1	1007788	2019-06-30 10:10:23.600149	\N	\N	100
ocean:ArcticOcean:2960860	S2	Arctic Ocean	ocean	root	1	664693	2019-06-30 09:51:43.540842	\N	\N	100
continent:Oceania:6255151	S2	Oceania	continent	root	0	1157698	2019-06-30 09:52:29.942576	\N	\N	100
state:Eua:7668021	S2	Eua	state	country:Tonga:4032283	1	978	2019-06-30 10:18:20.063208	\N	\N	100
state:Boe:2110441	S2	Boe	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.211456	\N	\N	100
state:Anetan:2110448	S2	Anetan	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.212167	\N	\N	100
state:Aiwo:2110451	S2	Aiwo	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.212846	\N	\N	100
state:Ewa:2110435	S2	Ewa	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.213761	\N	\N	100
state:Denigomodu:2110437	S2	Denigomodu	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.214525	\N	\N	100
state:Uaboe:2110420	S2	Uaboe	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.221395	\N	\N	100
state:Anabar:2110449	S2	Anabar	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.2224	\N	\N	100
state:Ijuw:2110432	S2	Ijuw	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.223198	\N	\N	100
state:Baiti:2110442	S2	Baiti	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.241963	\N	\N	100
state:Yaren:2110418	S2	Yaren	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.251498	\N	\N	100
state:Buada:2110440	S2	Buada	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.252924	\N	\N	100
state:Nibok:2110423	S2	Nibok	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.254397	\N	\N	100
state:Meneng:2110431	S2	Meneng	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.255752	\N	\N	100
state:Anibare:2110445	S2	Anibare	state	country:Nauru:2110425	1	459	2019-06-30 10:32:28.257949	\N	\N	100
country:Nauru:2110425	S2	Nauru	country	continent:Oceania:6255151	0	459	2019-06-30 10:32:28.25946	\N	\N	100
state:Saskatchewan:6141242	S2	Saskatchewan	state	region:WesternCanada:5953214	1	102725	2019-06-30 10:15:42.438087	\N	\N	100
state:Suhbaatar:2029155	S2	Sühbaatar	state	country:Mongolia:2029969	1	15061	2019-06-30 12:32:44.724714	\N	\N	100
state:Alaska:5879092	S2	Alaska	state	region:West:11887751	1	325311	2019-06-30 10:14:08.568125	\N	\N	100
country:Mongolia:2029969	S2	Mongolia	country	continent:Asia:6255147	0	219292	2019-06-30 12:32:44.72535	\N	\N	100
state:Jalisco:4004156	S2	Jalisco	state	country:Mexico:3996063	1	10371	2019-06-30 10:16:24.916794	\N	\N	100
state:Nayarit:3995012	S2	Nayarit	state	country:Mexico:3996063	1	3890	2019-07-01 05:20:20.160675	\N	\N	100
state:Mississippi:4436296	S2	Mississippi	state	region:South:11887752	1	13928	2019-07-01 02:46:13.936624	\N	\N	100
state:Retalhuleu:3590857	S2	Retalhuleu	state	country:Guatemala:3595528	1	1015	2019-07-02 23:31:39.052699	\N	\N	100
country:Japan:1861060	S2	Japan	country	continent:Asia:6255147	0	53903	2019-06-30 10:10:40.765111	\N	\N	100
region:Chubu:1864496	S2	Chubu	region	country:Japan:1861060	0	12538	2019-06-30 10:14:00.415455	\N	\N	100
state:Niigata:1855429	S2	Niigata	state	region:Chubu:1864496	1	4203	2019-06-30 10:14:00.414813	\N	\N	100
season:winter	S2	Winter	season	root	1	5484932	2019-06-30 09:51:38.327185	\N	\N	100
location:southern	S2	Southern	location	root	1	7173898	2019-06-30 09:51:38.327582	\N	\N	100
country:Mexico:3996063	S2	Mexico	country	continent:NorthAmerica:6255149	0	209800	2019-06-30 10:15:35.381014	\N	\N	100
state:Chiapas:3531011	S2	Chiapas	state	country:Mexico:3996063	1	8365	2019-06-30 10:15:35.38042	\N	\N	100
region:South:11887752	S2	South	region	country:UnitedStatesOfAmerica:6252001	0	253619	2019-06-30 10:15:52.533987	\N	\N	100
country:Guatemala:3595528	S2	Guatemala	country	continent:NorthAmerica:6255149	0	13216	2019-06-30 10:15:12.323873	\N	\N	100
bay:HudsonBay:5978133	S2	Hudson Bay	bay	root	1	155101	2019-06-30 10:15:48.544971	\N	\N	100
state:Oklahoma:4544379	S2	Oklahoma	state	region:South:11887752	1	19746	2019-06-30 10:15:57.035233	\N	\N	100
ocean:NorthPacificOcean:4030875	S2	North Pacific Ocean	ocean	root	1	668417	2019-06-30 09:52:29.826658	\N	\N	100
state:Kerman:128231	S2	Kerman	state	country:Iran:130758	1	19236	2019-06-30 14:46:48.062436	\N	\N	100
country:Iran:130758	S2	Iran	country	continent:Asia:6255147	0	173160	2019-06-30 14:46:01.660813	\N	\N	100
state:Campeche:3531730	S2	Campeche	state	country:Mexico:3996063	1	8832	2019-06-30 10:15:46.948067	\N	\N	100
bay:BahiaDeCampeche:3531731	S2	Bahía de Campeche	bay	root	1	19233	2019-07-01 02:48:37.924384	\N	\N	100
location:tropical	S2	Tropical	location	root	1	7757756	2019-06-30 09:51:38.327988	\N	\N	100
state:HaTinh:1580700	S2	Ha Tinh	state	region:BacTrungBo:11497251	1	1907	2019-06-30 12:41:50.004345	\N	\N	100
region:BacTrungBo:11497251	S2	Bắc Trung Bộ	region	country:Vietnam:1562822	0	6541	2019-06-30 12:37:45.933759	\N	\N	100
bay:FoxeBasin:5956887	S2	Foxe Basin	bay	root	1	51536	2019-06-30 10:16:29.922773	\N	\N	100
state:Utah:5549030	S2	Utah	state	region:West:11887751	1	24680	2019-06-30 10:15:42.412715	\N	\N	100
region:NorthernCanada:	S2	Northern Canada	region	country:Canada:6251999	0	1158040	2019-06-30 10:14:59.893928	\N	\N	100
state:Iowa:4862182	S2	Iowa	state	region:Midwest:11887750	1	15914	2019-06-30 10:16:09.786372	\N	\N	100
region:Midwest:11887750	S2	Midwest	region	country:UnitedStatesOfAmerica:6252001	0	280769	2019-06-30 10:15:44.095367	\N	\N	100
landcover:ice	S2	Ice	landcover:ice	root	1	1460416	2019-06-30 09:52:11.848803	\N	\N	100
landcover:desert	S2	Desert	landcover:desert	root	1	4797023	2019-06-30 09:51:49.966318	\N	\N	100
state:Nunavut:6091732	S2	Nunavut	state	region:NorthernCanada:	1	751529	2019-06-30 10:14:59.892262	\N	\N	100
location:coastal	S2	Coastal	location	root	1	6422703	2019-06-30 09:51:43.698644	\N	\N	100
season:summer	S2	Summer	season	root	1	8689606	2019-06-30 09:51:43.539266	\N	\N	100
sea:BeringSea:4031788	S2	Bering Sea	sea	root	1	378576	2019-06-30 10:14:03.373335	\N	\N	100
region:West:11887751	S2	West	region	country:UnitedStatesOfAmerica:6252001	0	707168	2019-06-30 10:14:08.568518	\N	\N	100
country:Canada:6251999	S2	Canada	country	continent:NorthAmerica:6255149	0	2137275	2019-06-30 10:10:24.254267	\N	\N	100
state:Montana:5667009	S2	Montana	state	region:West:11887751	1	55778	2019-06-30 10:15:44.773128	\N	\N	100
country:UnitedStatesOfAmerica:6252001	S2	United States of America	country	continent:NorthAmerica:6255149	0	1277321	2019-06-30 10:14:08.56888	\N	\N	100
landcover:forest	S2	Forest	landcover:forest	root	1	6064257	2019-06-30 09:51:45.53346	\N	\N	100
sea:CeramSea:1818202	S2	Ceram Sea	sea	root	1	20269	2019-06-30 10:10:34.591779	\N	\N	100
gulf:GulfOfMexico:3523271	S2	Gulf of Mexico	gulf	root	1	147215	2019-06-30 10:15:51.966747	\N	\N	100
bay:BaffinBay:4671916	S2	Baffin Bay	bay	root	1	154638	2019-06-30 10:15:04.228914	\N	\N	100
sea:BeaufortSea:5896493	S2	Beaufort Sea	sea	root	1	85653	2019-06-30 10:14:34.188253	\N	\N	100
instrument:MSI	S2	MSI	instrument	platform:S2A	1	13513010	2019-06-30 09:51:38.325212	\N	\N	100
platform:S2A	S2	S2A	platform	root	0	13513009	2019-06-30 09:51:38.325611	\N	\N	100
channel:TheNorthWesternPassages	S2	The North Western Passages	channel	root	1	262948	2019-06-30 10:15:40.56729	\N	\N	100
region:WesternCanada:5953214	S2	Western Canada	region	country:Canada:6251999	0	504230	2019-06-30 10:15:42.438754	\N	\N	100
state:Manitoba:6065171	S2	Manitoba	state	region:WesternCanada:5953214	1	120833	2019-06-30 10:15:47.202994	\N	\N	100
landcover:herbaceous	S2	Herbaceous	landcover:herbaceous	root	1	4955825	2019-06-30 09:51:38.925885	\N	\N	100
region:EasternCanada:	S2	Eastern Canada	region	country:Canada:6251999	0	520380	2019-06-30 10:10:24.253863	\N	\N	100
month:06	S2	06	month	root	1	2821193	2019-06-30 09:51:38.324452	\N	\N	100
country:Greenland:3425505	S2	Greenland	country	continent:NorthAmerica:6255149	0	1099068	2019-06-30 09:52:24.4162	\N	\N	100
state:QaasuitsupKommunia:7602003	S2	Qaasuitsup Kommunia	state	country:Greenland:3425505	1	317678	2019-06-30 10:10:31.062173	\N	\N	100
day:21	S2	21	day	root	1	954708	2019-07-21 03:23:21.191251	\N	\N	100
state:Nationalparken:7422379	S2	Nationalparken	state	country:Greenland:3425505	1	665774	2019-06-30 09:52:26.91045	\N	\N	100
sound:NortonSound:5870368	S2	Norton Sound	sound	root	1	6555	2019-06-30 10:18:05.592624	\N	\N	100
region:Mimaropa(RegionIvB):	S2	MIMAROPA (Region IV-B)	region	country:Philippines:1694008	0	3776	2019-06-30 12:12:37.314712	\N	\N	100
state:AigaILeTai:4035425	S2	Aiga-i-le-Tai	state	country:Samoa:4034894	1	488	2019-06-30 10:18:07.031919	\N	\N	100
state:Palauli:4035154	S2	Palauli	state	country:Samoa:4034894	1	488	2019-06-30 10:18:07.035719	\N	\N	100
sea:SuluSea:6677962	S2	Sulu Sea	sea	root	1	30587	2019-06-30 12:11:40.946245	\N	\N	100
state:Palawan:1696177	S2	Palawan	state	region:Mimaropa(RegionIvB):	1	1553	2019-06-30 12:14:02.13349	\N	\N	100
gulf:GulfOfSakhalin:2121527	S2	Gulf of Sakhalin	gulf	root	1	3611	2019-06-30 10:50:57.199835	\N	\N	100
sound:PrinceWilliamSound:5872088	S2	Prince William Sound	sound	root	1	3579	2019-06-30 10:18:27.906519	\N	\N	100
state:Tyumen:1488747	S2	Tyumen'	state	region:Urals:466003	1	27723	2019-06-30 15:16:59.912877	\N	\N	100
region:Urals:466003	S2	Urals	region	country:Russia:2017370	0	361260	2019-06-30 14:03:43.383228	\N	\N	100
state:Tongatapu:4032279	S2	Tongatapu	state	country:Tonga:4032283	1	976	2019-06-30 10:32:17.386298	\N	\N	100
country:Tonga:4032283	S2	Tonga	country	continent:Oceania:6255151	0	925	2019-06-30 10:32:17.387025	\N	\N	100
gulf:GulfOfKau	S2	Gulf of Kau	gulf	root	1	1543	2019-06-30 10:33:56.558877	\N	\N	100
sea:MoluccaSea:1636628	S2	Molucca Sea	sea	root	1	23354	2019-06-30 10:32:53.233385	\N	\N	100
state:MalukuUtara:1958070	S2	Maluku Utara	state	country:Indonesia:1643084	1	2824	2019-06-30 10:32:53.234209	\N	\N	100
state:Taitung:1668292	S2	Taitung	state	region:TaiwanProvince:1668284	1	989	2019-06-30 11:04:46.832138	\N	\N	100
state:Chita:2025339	S2	Chita	state	region:Siberian:11961345	1	78294	2019-06-30 11:34:49.522504	\N	\N	100
state:Zhejiang:1784764	S2	Zhejiang	state	region:EastChina:6493581	1	10872	2019-06-30 11:03:24.225384	\N	\N	100
region:EastChina:6493581	S2	East China	region	country:China:1814991	0	79972	2019-06-30 11:03:24.225986	\N	\N	100
sea:EastChinaSea:1676969	S2	East China Sea	sea	root	1	78754	2019-06-30 11:01:12.511409	\N	\N	100
sea:BismarckSea:2110018	S2	Bismarck Sea	sea	root	1	33192	2019-06-30 10:31:48.399	\N	\N	100
state:Jilin:2036500	S2	Jilin	state	region:NortheastChina:	1	26283	2019-06-30 11:35:50.004467	\N	\N	100
sea:AndamanSea:1158425	S2	Andaman Sea	sea	root	1	58887	2019-06-30 10:14:46.150155	\N	\N	100
state:EastNewBritain:2097853	S2	East New Britain	state	country:PapuaNewGuinea:2088628	1	1548	2019-06-30 10:32:08.113605	\N	\N	100
state:TayNinh:1566557	S2	Tây Ninh	state	region:DongNamBo:11497301	1	989	2019-06-30 12:41:30.179713	\N	\N	100
region:DongNamBo:11497301	S2	Đông Nam Bộ	region	country:Vietnam:1562822	0	5082	2019-06-30 12:33:56.735153	\N	\N	100
state:SvayRieng:1821992	S2	Svay Rieng	state	country:Cambodia:1831722	1	996	2019-06-30 12:41:30.178788	\N	\N	100
country:Vietnam:1562822	S2	Vietnam	country	continent:Asia:6255147	0	36164	2019-06-30 12:33:56.735604	\N	\N	100
state:LongAn:1575788	S2	Long An	state	region:DongBangSongCuuLong:1574717	1	1005	2019-06-30 12:41:30.179259	\N	\N	100
gulf:ShelikhovaGulf	S2	Shelikhova Gulf	gulf	root	1	33101	2019-06-30 10:32:46.541577	\N	\N	100
sea:HalmaheraSea:1818198	S2	Halmahera Sea	sea	root	1	5001	2019-06-30 10:10:34.59107	\N	\N	100
state:VavaU:4032231	S2	Vava'u	state	country:Tonga:4032283	1	488	2019-06-30 10:32:17.496445	\N	\N	100
gulf:GulfOfBuli	S2	Gulf of Buli	gulf	root	1	2684	2019-06-30 10:14:00.063002	\N	\N	100
state:Penrhyn:4031139	S2	Penrhyn	state	country:CookIslands:1899402	1	503	2019-06-30 10:32:03.648997	\N	\N	100
state:AucklandIslands:2193727	S2	Auckland Islands	state	region:NewZealandOutlyingIslands:	1	1970	2019-06-30 10:17:40.576568	\N	\N	100
region:NewZealandOutlyingIslands:	S2	New Zealand Outlying Islands	region	country:NewZealand:2186224	0	1002	2019-06-30 10:18:08.557179	\N	\N	100
bay:DarnleyBay:5935254	S2	Darnley Bay	bay	root	1	2820	2019-06-30 10:32:54.5869	\N	\N	100
state:SouthAustralia:2061327	S2	South Australia	state	country:Australia:2077456	1	106306	2019-06-30 10:32:41.620054	\N	\N	100
bay:FranklinBay:5957409	S2	Franklin Bay	bay	root	1	2152	2019-06-30 10:32:54.586238	\N	\N	100
state:Vaisigano:4034910	S2	Vaisigano	state	country:Samoa:4034894	1	975	2019-06-30 10:18:07.031222	\N	\N	100
state:GagaEmauga:4035314	S2	Gaga'emauga	state	country:Samoa:4034894	1	975	2019-06-30 10:18:07.032541	\N	\N	100
state:SatupaItea:4035046	S2	Satupa'itea	state	country:Samoa:4034894	1	975	2019-06-30 10:18:07.033244	\N	\N	100
state:GagaIfomauga:4035313	S2	Gaga'ifomauga	state	country:Samoa:4034894	1	975	2019-06-30 10:18:07.033862	\N	\N	100
state:FaAsaleleaga:4035383	S2	Fa'asaleleaga	state	country:Samoa:4034894	1	975	2019-06-30 10:18:07.034484	\N	\N	100
region:NorthwestChina:	S2	Northwest China	region	country:China:1814991	0	374188	2019-06-30 12:31:30.493009	\N	\N	100
state:Shaanxi:1796480	S2	Shaanxi	state	region:NorthwestChina:	1	27345	2019-06-30 12:31:30.492415	\N	\N	100
sea:BandaSea:1818206	S2	Banda Sea	sea	root	1	69498	2019-06-30 10:10:23.048063	\N	\N	100
state:Hualien:1674504	S2	Hualien	state	region:TaiwanProvince:1668284	1	508	2019-06-30 11:11:39.940283	\N	\N	100
state:TaichungCity:9613500	S2	Taichung City	state	region:SpecialMunicipalities:	1	1489	2019-06-30 11:08:49.536798	\N	\N	100
region:TaiwanProvince:1668284	S2	Taiwan Province	region	country:Taiwan:1668284	0	4045	2019-06-30 11:03:24.524681	\N	\N	100
country:Taiwan:1668284	S2	Taiwan	country	continent:Asia:6255147	0	4063	2019-06-30 11:03:24.525235	\N	\N	100
state:Solola:3588697	S2	Sololá	state	country:Guatemala:3595528	1	1035	2019-06-30 10:16:08.207415	\N	\N	100
state:Totonicapan:3588257	S2	Totonicapán	state	country:Guatemala:3595528	1	1026	2019-06-30 10:16:08.206558	\N	\N	100
state:Irkutsk:2023468	S2	Irkutsk	state	region:Siberian:11961345	1	135399	2019-06-30 11:45:58.528635	\N	\N	100
state:Apayao:7115731	S2	Apayao	state	region:CordilleraAdministrativeRegion(Car):	1	1517	2019-06-30 11:07:24.77549	\N	\N	100
state:IlocosNorte:1711034	S2	Ilocos Norte	state	region:Ilocos(RegionI):	1	2068	2019-06-30 11:07:24.775991	\N	\N	100
state:InnerMongol:2035607	S2	Inner Mongol	state	region:NorthChina:1807463	1	166687	2019-06-30 12:31:42.815316	\N	\N	100
bay:ChaunBay:2126277	S2	Chaun Bay	bay	root	1	5709	2019-06-30 10:32:50.691291	\N	\N	100
state:WestCoast:6612113	S2	West Coast	state	region:SouthIsland:2182504	1	4359	2019-06-30 10:18:09.184146	\N	\N	100
sea:TimorSea:2078065	S2	Timor Sea	sea	root	1	40874	2019-06-30 10:10:20.341015	\N	\N	100
state:Sandaun:2087246	S2	Sandaun	state	country:PapuaNewGuinea:2088628	1	4913	2019-06-30 10:33:22.118478	\N	\N	100
country:PapuaNewGuinea:2088628	S2	Papua New Guinea	country	continent:Oceania:6255151	0	45909	2019-06-30 10:31:49.077728	\N	\N	100
state:Rarotonga:4035552	S2	Rarotonga	state	country:CookIslands:1899402	1	914	2019-10-24 23:14:56.245857	\N	\N	100
country:CookIslands:1899402	S2	Cook Islands	country	continent:Oceania:6255151	0	1365	2019-10-24 23:13:57.160065	\N	\N	100
bay:MontereyBay:5374363	S2	Monterey Bay	bay	root	1	1024	2019-06-30 10:17:44.312099	\N	\N	100
state:Riau:1629652	S2	Riau	state	country:Indonesia:1643084	1	8364	2019-06-30 11:53:10.60999	\N	\N	100
strait:StraitOfMalacca:1818192	S2	Strait of Malacca	strait	root	1	19730	2019-06-30 12:40:39.4858	\N	\N	100
ocean:IndianOcean:1545739	S2	Indian Ocean	ocean	root	1	620206	2019-06-30 10:13:57.170806	\N	\N	100
strait:QueenCharlotteStrait:6115072	S2	Queen Charlotte Strait	strait	root	1	3608	2019-06-30 10:17:26.169169	\N	\N	100
bay:MackenzieBay:6063193	S2	Mackenzie Bay	bay	root	1	6077	2019-06-30 10:32:19.778114	\N	\N	100
state:Krasnoyarsk:1502020	S2	Krasnoyarsk	state	region:Siberian:11961345	1	550845	2019-06-30 10:14:22.374819	\N	\N	100
region:Siberian:11961345	S2	Siberian	region	country:Russia:2017370	0	1034371	2019-06-30 10:14:22.375422	\N	\N	100
country:NewZealand:2186224	S2	New Zealand	country	continent:Oceania:6255151	0	45489	2019-06-30 10:18:09.18558	\N	\N	100
region:SouthIsland:2182504	S2	South Island	region	country:NewZealand:2186224	0	25375	2019-06-30 10:18:09.184873	\N	\N	100
state:Southland:2182501	S2	Southland	state	region:SouthIsland:2182504	1	5057	2019-06-30 10:18:20.920464	\N	\N	100
state:ElProgreso:3596416	S2	El Progreso	state	country:Guatemala:3595528	1	1035	2019-06-30 10:16:17.536492	\N	\N	100
state:BajaVerapaz:3599602	S2	Baja Verapaz	state	country:Guatemala:3595528	1	1034	2019-06-30 10:16:17.543415	\N	\N	100
state:Jalapa:3595236	S2	Jalapa	state	country:Guatemala:3595528	1	529	2019-06-30 10:16:27.852588	\N	\N	100
state:StannCreek:3580975	S2	Stann Creek	state	country:Belize:3582678	1	1035	2019-06-30 10:16:57.800596	\N	\N	100
gulf:GulfOfAlaska:5879094	S2	Gulf of Alaska	gulf	root	1	72705	2019-06-30 10:17:49.383342	\N	\N	100
country:India:6269134	S2	India	country	continent:Asia:6255147	0	320051	2019-06-30 15:19:05.219813	\N	\N	100
bay:BayOfBengal:1281789	S2	Bay of Bengal	bay	root	1	86136	2019-06-30 10:14:45.819178	\N	\N	100
state:AndhraPradesh:1278629	S2	Andhra Pradesh	state	region:South:8335041	1	32172	2019-06-30 15:22:07.380783	\N	\N	100
region:South:8335041	S2	South	region	country:India:6269134	0	66222	2019-06-30 15:19:05.219149	\N	\N	100
bay:CookInlet:5859822	S2	Cook Inlet	bay	root	1	5769	2019-06-30 10:17:57.003904	\N	\N	100
state:Heilongjiang:2036965	S2	Heilongjiang	state	region:NortheastChina:	1	69836	2019-06-30 11:35:59.263982	\N	\N	100
region:NortheastChina:	S2	Northeast China	region	country:China:1814991	0	119828	2019-06-30 11:35:50.005133	\N	\N	100
state:Ahuachapan:3587425	S2	Ahuachapán	state	country:ElSalvador:3585968	1	529	2019-06-30 10:15:27.502689	\N	\N	100
gulf:GulfOfTonkin:1818184	S2	Gulf of Tonkin	gulf	root	1	16603	2019-06-30 12:33:06.548725	\N	\N	100
sea:YellowSea:1818183	S2	Yellow Sea	sea	root	1	50575	2019-06-30 11:02:21.087905	\N	\N	100
state:CrookedIslandAndLongCay:8030545	S2	Crooked Island and Long Cay	state	country:Bahamas:3572887	1	1009	2019-06-30 10:16:28.657888	\N	\N	100
state:Corozal:3582302	S2	Corozal	state	country:Belize:3582678	1	843	2019-06-30 10:17:09.326363	\N	\N	100
state:Huehuetenango:3595415	S2	Huehuetenango	state	country:Guatemala:3595528	1	1036	2019-06-30 10:16:04.340172	\N	\N	100
state:Papua:1643012	S2	Papua	state	country:Indonesia:1643084	1	30564	2019-06-30 10:32:44.339922	\N	\N	100
country:Indonesia:1643084	S2	Indonesia	country	continent:Asia:6255147	0	199219	2019-06-30 10:10:20.787406	\N	\N	100
landcover:urban	S2	Urban	landcover:urban	root	1	8969	2019-06-30 10:14:23.059604	\N	\N	100
state:Kingston:3489853	S2	Kingston	state	country:Jamaica:3489940	1	506	2019-06-30 10:16:00.80689	\N	\N	100
state:Portland:3488997	S2	Portland	state	country:Jamaica:3489940	1	1014	2019-06-30 10:15:29.429389	\N	\N	100
state:SaintThomas:3488688	S2	Saint Thomas	state	country:Jamaica:3489940	1	507	2019-06-30 10:16:00.808488	\N	\N	100
state:NewHampshire:5090174	S2	New Hampshire	state	region:Northeast:11887749	1	3032	2019-06-30 10:15:21.644439	\N	\N	100
sound:EclipseSound:5946494	S2	Eclipse Sound	sound	root	1	2648	2019-06-30 10:15:41.345642	\N	\N	100
region:CentralLuzon(RegionIii):	S2	Central Luzon (Region III)	region	country:Philippines:1694008	0	4636	2019-06-30 11:06:48.058305	\N	\N	100
country:Philippines:1694008	S2	Philippines	country	continent:Asia:6255147	0	42354	2019-06-30 11:03:43.790158	\N	\N	100
state:Zambales:1679435	S2	Zambales	state	region:CentralLuzon(RegionIii):	1	1745	2019-06-30 11:06:48.057752	\N	\N	100
state:Pangasinan:1695357	S2	Pangasinan	state	region:Ilocos(RegionI):	1	2063	2019-06-30 11:09:31.366018	\N	\N	100
region:Ilocos(RegionI):	S2	Ilocos (Region I)	region	country:Philippines:1694008	0	7677	2019-06-30 11:03:43.789664	\N	\N	100
state:AustralIslands:4033250	S2	Austral Islands	state	country:FrenchPolynesia:4030656	1	1798	2019-10-22 22:38:09.756376	\N	\N	100
state:Idaho:5596512	S2	Idaho	state	region:West:11887751	1	24259	2019-06-30 10:17:20.882813	\N	\N	100
gulf:LiddonGulf:6053568	S2	Liddon Gulf	gulf	root	1	3461	2019-06-30 10:31:55.789272	\N	\N	100
sea:SolomonSea:2086419	S2	Solomon Sea	sea	root	1	69761	2019-06-30 10:31:47.918735	\N	\N	100
state:Loreto:3695238	S2	Loreto	state	country:Peru:3932488	1	37343	2019-06-30 10:14:37.157575	\N	\N	100
sound:SmithSound:6150199	S2	Smith Sound	sound	root	1	2847	2019-06-30 10:17:19.019348	\N	\N	100
state:Guangxi:1809867	S2	Guangxi	state	region:SouthCentralChina:	1	25435	2019-06-30 12:33:15.487391	\N	\N	100
channel:KennedyChannel:5990863	S2	Kennedy Channel	channel	root	1	16127	2019-06-30 10:17:24.306965	\N	\N	100
state:Tasmania:2147291	S2	Tasmania	state	country:Australia:2077456	1	8090	2019-06-30 10:32:26.015519	\N	\N	100
state:Victoria:2145234	S2	Victoria	state	country:Australia:2077456	1	29432	2019-06-30 10:32:26.587723	\N	\N	100
state:SantaBarbara:3601689	S2	Santa Bárbara	state	country:Honduras:3608932	1	1502	2019-06-30 10:15:25.543467	\N	\N	100
country:UsnbGuantanamoBay:	S2	USNB Guantanamo Bay	country	continent:NorthAmerica:6255149	0	1004	2019-06-30 10:15:29.630779	\N	\N	100
state:Guantanamo:3557685	S2	Guantánamo	state	country:Cuba:3562981	1	1596	2019-06-30 10:15:29.632621	\N	\N	100
sea:SouthChinaSea:1594992	S2	South China Sea	sea	root	1	291521	2019-06-30 11:01:31.925426	\N	\N	100
state:NorthCarolina:4482348	S2	North Carolina	state	region:South:11887752	1	17237	2019-06-30 10:16:46.839206	\N	\N	100
gulf:GulfOfBoothia:5967759	S2	Gulf of Boothia	gulf	root	1	22464	2019-06-30 10:32:10.439305	\N	\N	100
gulf:GulfOfAnadyr:4031773	S2	Gulf of Anadyr	gulf	root	1	22464	2019-06-30 10:32:10.439991	\N	\N	100
state:MagaBuryatdan:2123627	S2	Maga Buryatdan	state	region:FarEastern:11961349	1	86071	2019-06-30 10:10:41.289809	\N	\N	100
gulf:HallBasin:5969469	S2	Hall Basin	gulf	root	1	15474	2019-06-30 10:17:24.307736	\N	\N	100
strait:MClureStrait:6070741	S2	M'Clure Strait	strait	root	1	17110	2019-06-30 10:18:25.308913	\N	\N	100
state:BajaCalifornia:4017700	S2	Baja California	state	country:Mexico:3996063	1	11046	2019-06-30 10:15:46.400072	\N	\N	100
bay:SanFranciscoBay:10630414	S2	San Francisco Bay	bay	root	1	1024	2019-06-30 10:17:59.774108	\N	\N	100
sea:ChukchiSea:5859385	S2	Chukchi Sea	sea	root	1	81080	2019-06-30 10:14:08.207326	\N	\N	100
state:SantaRosa:3589172	S2	Santa Rosa	state	country:Guatemala:3595528	1	1050	2019-06-30 10:15:12.322706	\N	\N	100
state:Aguascalientes:4019231	S2	Aguascalientes	state	country:Mexico:3996063	1	1558	2019-06-30 10:17:06.404436	\N	\N	100
state:Shanxi:1795912	S2	Shanxi	state	region:NorthChina:1807463	1	23849	2019-06-30 12:31:30.49084	\N	\N	100
sea:PhilippineSea:1818190	S2	Philippine Sea	sea	root	1	153511	2019-06-30 10:14:13.044077	\N	\N	100
strait:FuryAndHeclaStrait:5958817	S2	Fury and Hecla Strait	strait	root	1	4251	2019-06-30 10:15:42.948083	\N	\N	100
state:DistritoCapital:3640847	S2	Distrito Capital	state	country:Venezuela:3625428	1	877	2019-06-30 10:14:07.601172	\N	\N	100
strait:HecateStrait:5973314	S2	Hecate Strait	strait	root	1	7181	2019-06-30 10:16:48.928966	\N	\N	100
state:BlackPoint:8030541	S2	Black Point	state	country:Bahamas:3572887	1	1017	2019-06-30 10:16:05.433158	\N	\N	100
state:Wisconsin:5279468	S2	Wisconsin	state	region:Midwest:11887750	1	19505	2019-06-30 10:17:00.394855	\N	\N	100
bay:HadleyBay:5968810	S2	Hadley Bay	bay	root	1	3106	2019-06-30 10:32:53.571775	\N	\N	100
sound:QueenCharlotteSound:6115071	S2	Queen Charlotte Sound	sound	root	1	7203	2019-06-30 10:17:05.653941	\N	\N	100
state:Tacna:3928127	S2	Tacna	state	country:Peru:3932488	1	2111	2019-06-30 10:16:03.377224	\N	\N	100
state:RhodeIsland:5224323	S2	Rhode Island	state	region:Northeast:11887749	1	1431	2019-06-30 10:15:19.532698	\N	\N	100
state:Chiquimula:3598464	S2	Chiquimula	state	country:Guatemala:3595528	1	1045	2019-06-30 10:16:01.23075	\N	\N	100
state:Copan:3613229	S2	Copán	state	country:Honduras:3608932	1	2082	2019-06-30 10:15:25.544137	\N	\N	100
state:Zacapa:3587586	S2	Zacapa	state	country:Guatemala:3595528	1	1038	2019-06-30 10:16:01.231445	\N	\N	100
state:Sakhalin:2121529	S2	Sakhalin	state	region:FarEastern:11961349	1	18866	2019-06-30 10:14:00.518939	\N	\N	100
sea:SeaOfOkhotsk:2127380	S2	Sea of Okhotsk	sea	root	1	237618	2019-06-30 10:10:40.412229	\N	\N	100
bay:BristolBay:5858042	S2	Bristol Bay	bay	root	1	15592	2019-06-30 10:18:21.085803	\N	\N	100
bay:MassachusettsBay:4832278	S2	Massachusetts Bay	bay	root	1	1083	2019-06-30 10:15:21.643231	\N	\N	100
state:Massachusetts:6254926	S2	Massachusetts	state	region:Northeast:11887749	1	2276	2019-06-30 10:16:10.051521	\N	\N	100
state:VaAOFonoti:4034943	S2	Va'a-o-Fonoti	state	country:Samoa:4034894	1	488	2019-06-30 10:31:51.785518	\N	\N	100
state:AAna:4035434	S2	A'ana	state	country:Samoa:4034894	1	976	2019-06-30 10:18:07.035112	\N	\N	100
state:Atua:4035402	S2	Atua	state	country:Samoa:4034894	1	488	2019-06-30 10:31:51.786643	\N	\N	100
state:Tuamasaga:4034977	S2	Tuamasaga	state	country:Samoa:4034894	1	488	2019-06-30 10:31:51.787227	\N	\N	100
country:Samoa:4034894	S2	Samoa	country	continent:Oceania:6255151	0	1463	2019-06-30 10:18:07.036344	\N	\N	100
sea:JavaSea:1818196	S2	Java Sea	sea	root	1	56028	2019-06-30 11:54:24.866515	\N	\N	100
sound:JonesSound:5987851	S2	Jones Sound	sound	root	1	10521	2019-06-30 10:15:42.542414	\N	\N	100
state:SanSalvador:3583360	S2	San Salvador	state	country:ElSalvador:3585968	1	1053	2019-06-30 10:16:27.760475	\N	\N	100
state:Jutiapa:3595067	S2	Jutiapa	state	country:Guatemala:3595528	1	1586	2019-06-30 10:15:27.49664	\N	\N	100
state:Cuscatlan:3586831	S2	Cuscatlán	state	country:ElSalvador:3585968	1	1037	2019-06-30 10:16:27.759867	\N	\N	100
bay:KaneBasin:3831549	S2	Kane Basin	bay	root	1	36274	2019-06-30 10:15:43.087606	\N	\N	100
state:Sonsonate:3583101	S2	Sonsonate	state	country:ElSalvador:3585968	1	1052	2019-06-30 10:15:27.503311	\N	\N	100
state:SantaAna:3583332	S2	Santa Ana	state	country:ElSalvador:3585968	1	1061	2019-06-30 10:15:27.504499	\N	\N	100
country:ElSalvador:3585968	S2	El Salvador	country	continent:NorthAmerica:6255149	0	2733	2019-06-30 10:15:27.505093	\N	\N	100
state:Chalatenango:3587090	S2	Chalatenango	state	country:ElSalvador:3585968	1	1560	2019-06-30 10:15:27.50207	\N	\N	100
state:SantiagoDeCuba:3536725	S2	Santiago de Cuba	state	country:Cuba:3562981	1	1028	2019-06-30 10:15:29.631432	\N	\N	100
bay:MelvilleBay:6071973	S2	Melville Bay	bay	root	1	13864	2019-06-30 10:15:24.313702	\N	\N	100
sound:PrinceAlbertSound:6113343	S2	Prince ALbert Sound	sound	root	1	4479	2019-06-30 10:32:45.938993	\N	\N	100
state:CatIsland:3572678	S2	Cat Island	state	country:Bahamas:3572887	1	1017	2019-06-30 10:15:58.127396	\N	\N	100
state:WestVirginia:4826850	S2	West Virginia	state	region:South:11887752	1	5809	2019-06-30 10:17:01.709513	\N	\N	100
channel:RobesonChannel:6125655	S2	Robeson Channel	channel	root	1	8442	2019-06-30 10:17:32.747447	\N	\N	100
gulf:AmundsenGulf:5884641	S2	Amundsen Gulf	gulf	root	1	28296	2019-06-30 10:31:48.225549	\N	\N	100
region:NorthChina:1807463	S2	North China	region	country:China:1814991	0	218223	2019-06-30 12:31:30.491525	\N	\N	100
state:Hebei:1808773	S2	Hebei	state	region:NorthChina:1807463	1	25139	2019-06-30 12:34:38.88081	\N	\N	100
state:Holguin:3556965	S2	Holguín	state	country:Cuba:3562981	1	2024	2019-06-30 10:15:29.632035	\N	\N	100
state:Suchitepequez:3588668	S2	Suchitepéquez	state	country:Guatemala:3595528	1	2059	2019-06-30 10:15:47.278542	\N	\N	100
state:Escuintla:3595802	S2	Escuintla	state	country:Guatemala:3595528	1	1054	2019-06-30 10:15:12.323299	\N	\N	100
state:SouthDakota:5769223	S2	South Dakota	state	region:Midwest:11887750	1	23990	2019-06-30 10:16:19.083131	\N	\N	100
state:Oregon:5744337	S2	Oregon	state	region:West:11887751	1	29623	2019-06-30 10:17:04.021296	\N	\N	100
state:Yukon:6185811	S2	Yukon	state	region:NorthernCanada:	1	97542	2019-06-30 10:18:26.671488	\N	\N	100
bay:BathurstInlet:5894954	S2	Bathurst Inlet	bay	root	1	1137	2019-06-30 10:18:15.850037	\N	\N	100
state:RaggedIsland:3571629	S2	Ragged Island	state	country:Bahamas:3572887	1	516	2019-06-30 10:15:35.076	\N	\N	100
state:Nebraska:5073708	S2	Nebraska	state	region:Midwest:11887750	1	31479	2019-06-30 10:16:14.249508	\N	\N	100
state:Kamchatka:553817	S2	Kamchatka	state	region:FarEastern:11961349	1	90107	2019-06-30 10:32:01.23347	\N	\N	100
gulf:GulfOfCarpentaria:11001799	S2	Gulf of Carpentaria	gulf	root	1	35610	2019-06-30 10:32:43.305784	\N	\N	100
state:NorthDakota:5690763	S2	North Dakota	state	region:Midwest:11887750	1	28185	2019-06-30 10:15:44.094681	\N	\N	100
state:Queensland:2152274	S2	Queensland	state	country:Australia:2077456	1	170901	2019-06-30 10:10:37.755934	\N	\N	100
sea:ArafuraSea:1818208	S2	Arafura Sea	sea	root	1	72930	2019-06-30 10:32:43.324884	\N	\N	100
state:Lempira:3606066	S2	Lempira	state	country:Honduras:3608932	1	1583	2019-06-30 10:16:04.564435	\N	\N	100
state:Ocotepeque:3604318	S2	Ocotepeque	state	country:Honduras:3608932	1	2600	2019-06-30 10:16:01.228926	\N	\N	100
country:Honduras:3608932	S2	Honduras	country	continent:NorthAmerica:6255149	0	14392	2019-06-30 10:15:25.547941	\N	\N	100
state:Guerrero:3527213	S2	Guerrero	state	country:Mexico:3996063	1	8126	2019-06-30 10:16:14.089976	\N	\N	100
state:LongIsland:3572005	S2	Long Island	state	country:Bahamas:3572887	1	1533	2019-06-30 10:15:58.12611	\N	\N	100
state:Exuma:3572427	S2	Exuma	state	country:Bahamas:3572887	1	1533	2019-06-30 10:15:58.126775	\N	\N	100
sound:MurchisonSound:3831286	S2	Murchison Sound	sound	root	1	9772	2019-06-30 10:15:43.916254	\N	\N	100
sea:CoralSea:2194166	S2	Coral Sea	sea	root	1	278069	2019-06-30 09:52:30.65808	\N	\N	100
channel:GoldsmithChannel:5962754	S2	Goldsmith Channel	channel	root	1	1641	2019-06-30 10:32:57.087468	\N	\N	100
sound:ViscountMelvilleSound:6174519	S2	Viscount Melville Sound	sound	root	1	18808	2019-06-30 10:31:52.905578	\N	\N	100
state:Nevada:5509151	S2	Nevada	state	region:West:11887751	1	35037	2019-06-30 10:17:49.277959	\N	\N	100
state:Miranda:3632191	S2	Miranda	state	country:Venezuela:3625428	1	1017	2019-06-30 10:10:31.22799	\N	\N	100
state:Austurland:3337405	S2	Austurland	state	country:Iceland:2629691	1	4412	2019-06-30 21:50:56.085102	\N	\N	100
gulf:GulfOfHonduras:3595439	S2	Gulf of Honduras	gulf	root	1	3129	2019-06-30 10:16:19.487849	\N	\N	100
country:China:1814991	S2	China	country	continent:Asia:6255147	0	1071927	2019-06-30 10:14:46.420305	\N	\N	100
state:Hubei:1806949	S2	Hubei	state	region:SouthCentralChina:	1	18093	2019-06-30 12:32:40.379462	\N	\N	100
region:SouthCentralChina:	S2	South Central China	region	country:China:1814991	0	108662	2019-06-30 12:31:58.906001	\N	\N	100
state:LaPaz:3585087	S2	La Paz	state	country:ElSalvador:3585968	1	2081	2019-06-30 10:16:27.759195	\N	\N	100
state:LaLibertad:3585155	S2	La Libertad	state	country:ElSalvador:3585968	1	1566	2019-06-30 10:15:27.503909	\N	\N	100
state:NewMexico:5481136	S2	New Mexico	state	region:West:11887751	1	33821	2019-06-30 10:15:47.991098	\N	\N	100
strait:PrinceOfWalesStrait:6113394	S2	Prince of Wales Strait	strait	root	1	4750	2019-06-30 10:32:00.617396	\N	\N	100
state:NewSouthWales:2155400	S2	New South Wales	state	country:Australia:2077456	1	86410	2019-06-30 10:10:22.179074	\N	\N	100
continent:Antarctica:	S2	Antarctica	continent	root	0	782074	2019-08-25 02:35:58.352672	\N	\N	100
state:CentralEleuthera:8030544	S2	Central Eleuthera	state	country:Bahamas:3572887	1	1006	2019-06-30 10:16:20.227836	\N	\N	100
state:SouthEleuthera:8030557	S2	South Eleuthera	state	country:Bahamas:3572887	1	1010	2019-06-30 10:16:20.228479	\N	\N	100
bay:RichardCollinsonInlet:6121897	S2	Richard Collinson Inlet	bay	root	1	7845	2019-06-30 10:32:53.036981	\N	\N	100
bay:WynniattBay:6185012	S2	Wynniatt Bay	bay	root	1	3989	2019-06-30 10:32:53.037747	\N	\N	100
state:Tombali:2368955	S2	Tombali	state	region:Sul:2369151	1	980	2019-07-04 17:46:32.049234	\N	\N	100
region:Sul:2369151	S2	Sul	region	country:GuineaBissau:2372248	0	978	2019-07-04 17:46:32.049779	\N	\N	100
state:BajaCaliforniaSur:4017698	S2	Baja California Sur	state	country:Mexico:3996063	1	8792	2019-06-30 10:15:48.047371	\N	\N	100
state:SaintCroix:7267902	S2	Saint Croix	state	country:UnitedStatesVirginIslands:4796751	1	1010	2019-06-30 10:14:09.277208	\N	\N	100
state:RumCay:8030554	S2	Rum Cay	state	country:Bahamas:3572887	1	1029	2019-06-30 10:15:58.125372	\N	\N	100
state:SanSalvador:3571493	S2	San Salvador	state	country:Bahamas:3572887	1	508	2019-06-30 10:16:05.314294	\N	\N	100
bay:DiskoBay:3420623	S2	Disko Bay	bay	root	1	5901	2019-06-30 10:14:22.031205	\N	\N	100
state:Tagant:2376551	S2	Tagant	state	country:Mauritania:2378080	1	11915	2019-07-01 20:53:53.923254	\N	\N	100
strait:StraitOfBelleIsle:6640420	S2	Strait of Belle Isle	strait	root	1	1417	2019-06-30 10:14:20.920778	\N	\N	100
state:Ucayali:3691099	S2	Ucayali	state	country:Peru:3932488	1	12495	2019-06-30 10:15:19.124154	\N	\N	100
state:Tennessee:4662168	S2	Tennessee	state	region:South:11887752	1	13084	2019-06-30 10:16:00.768846	\N	\N	100
state:California:5332921	S2	California	state	region:West:11887751	1	46083	2019-06-30 10:17:13.652633	\N	\N	100
state:Camaguey:3566062	S2	Camagüey	state	country:Cuba:3562981	1	3060	2019-06-30 10:15:31.465159	\N	\N	100
state:LasTunas:3550595	S2	Las Tunas	state	country:Cuba:3562981	1	2547	2019-06-30 10:15:31.465877	\N	\N	100
state:Colorado:5417618	S2	Colorado	state	region:West:11887751	1	35806	2019-06-30 10:15:44.553093	\N	\N	100
gulf:GulfOfSaintLawrence:3831546	S2	Gulf of Saint Lawrence	gulf	root	1	43223	2019-06-30 10:10:26.302676	\N	\N	100
state:SaintJames:3488700	S2	Saint James	state	country:Jamaica:3489940	1	2014	2019-06-30 10:15:35.290353	\N	\N	100
state:SaintMary:3488693	S2	Saint Mary	state	country:Jamaica:3489940	1	1009	2019-06-30 10:15:29.422595	\N	\N	100
state:Trelawny:3488222	S2	Trelawny	state	country:Jamaica:3489940	1	1016	2019-06-30 10:15:35.293511	\N	\N	100
state:SaintAnn:3488715	S2	Saint Ann	state	country:Jamaica:3489940	1	1016	2019-06-30 10:15:35.294062	\N	\N	100
state:WesternAustralia:2058645	S2	Western Australia	state	country:Australia:2077456	1	252149	2019-06-30 10:10:20.303713	\N	\N	100
state:Izabal:3595259	S2	Izabal	state	country:Guatemala:3595528	1	2107	2019-06-30 10:16:19.488502	\N	\N	100
state:Tambacounda:2244990	S2	Tambacounda	state	country:Senegal:2245662	1	3905	2019-07-01 20:57:52.567705	\N	\N	100
state:MiquelonLanglade:3424938	S2	Miquelon-Langlade	state	country:SaintPierreAndMiquelon:3424932	1	2008	2019-06-30 10:10:37.398062	\N	\N	100
state:SaintPierre:3424935	S2	Saint-Pierre	state	country:SaintPierreAndMiquelon:3424932	1	503	2019-06-30 10:10:37.397428	\N	\N	100
sound:MintoInlet:6075018	S2	Minto Inlet	sound	root	1	4374	2019-06-30 10:33:31.852776	\N	\N	100
state:ValeOfGlamorgan:2635028	S2	Vale of Glamorgan	state	region:WestWalesAndTheValleys:	1	504	2019-07-04 18:27:20.242297	\N	\N	100
state:Devon:2651292	S2	Devon	state	region:SouthWest:11591956	1	1013	2019-07-02 20:16:22.586229	\N	\N	100
region:SouthWest:11591956	S2	South West	region	country:UnitedKingdom:2635167	0	4444	2019-07-01 19:34:22.938623	\N	\N	100
country:SaintPierreAndMiquelon:3424932	S2	Saint Pierre and Miquelon	country	continent:NorthAmerica:6255149	0	2008	2019-06-30 10:10:37.398647	\N	\N	100
state:Salta:3838231	S2	Salta	state	country:Argentina:3865483	1	18938	2019-06-30 10:10:33.774255	\N	\N	100
state:NorthwestTerritories:6091069	S2	Northwest Territories	state	region:NorthernCanada:	1	309365	2019-06-30 10:17:30.76592	\N	\N	100
region:NorthWest:2641227	S2	North West	region	country:UnitedKingdom:2635167	0	3498	2019-07-02 19:38:22.62484	\N	\N	100
state:Lancashire:2644974	S2	Lancashire	state	region:NorthWest:2641227	1	962	2019-07-02 19:40:08.677695	\N	\N	100
state:Toledo:3580913	S2	Toledo	state	country:Belize:3582678	1	1534	2019-06-30 10:15:27.670027	\N	\N	100
state:Apurimac:3947421	S2	Apurímac	state	country:Peru:3932488	1	2557	2019-06-30 10:15:00.355613	\N	\N	100
state:Cornwall:11609027	S2	Cornwall	state	region:SouthWest:11591956	1	4517	2019-07-02 19:30:35.785358	\N	\N	100
channel:BristolChannel:6640393	S2	Bristol Channel	channel	root	1	6007	2019-07-01 19:13:31.883456	\N	\N	100
state:BritishColumbia:5909050	S2	British Columbia	state	region:WesternCanada:5953214	1	168652	2019-06-30 10:16:43.179575	\N	\N	100
state:Arizona:5551752	S2	Arizona	state	region:West:11887751	1	33079	2019-06-30 10:15:44.957728	\N	\N	100
state:Sonora:3982846	S2	Sonora	state	country:Mexico:3996063	1	20532	2019-06-30 10:15:46.412851	\N	\N	100
state:Kansas:4273857	S2	Kansas	state	region:Midwest:11887750	1	25557	2019-06-30 10:16:12.263477	\N	\N	100
country:Australia:2077456	S2	Australia	country	continent:Oceania:6255151	0	760440	2019-06-30 10:10:20.304341	\N	\N	100
state:Geneve:2660645	S2	Genève	state	country:Switzerland:2658434	1	1031	2019-07-02 19:18:46.684421	\N	\N	100
state:NorthernTerritory:2064513	S2	Northern Territory	state	country:Australia:2077456	1	129790	2019-06-30 10:32:41.708945	\N	\N	100
state::	S2	_all	state	country:Mexico:3996063	1	2487	2022-05-04 22:46:18.253312	\N	\N	100
state:MecklenburgVorpommern:2872567	S2	Mecklenburg-Vorpommern	state	country:Germany:2921044	1	6672	2019-06-30 09:51:59.978516	\N	\N	100
state:Syddanmark:6418542	S2	Syddanmark	state	country:Denmark:2623032	1	2635	2019-06-30 09:51:58.619327	\N	\N	100
country:Gambia:2413451	S2	Gambia	country	continent:Africa:6255146	0	2080	2019-07-02 20:19:27.836089	\N	\N	100
state:CentralRiver:2412707	S2	Central River	state	country:Gambia:2413451	1	1222	2019-07-04 17:50:06.838082	\N	\N	100
state:Kolda:2249781	S2	Kolda	state	country:Senegal:2245662	1	2054	2019-07-01 21:00:35.069613	\N	\N	100
state:UpperRiver:2411711	S2	Upper River	state	country:Gambia:2413451	1	1033	2019-07-04 18:23:06.75772	\N	\N	100
state:DumfriesAndGalloway:2650797	S2	Dumfries and Galloway	state	region:SouthWestern:2637295	1	2039	2019-06-30 18:39:39.144067	\N	\N	100
state:KommuneKujalleq:7602005	S2	Kommune Kujalleq	state	country:Greenland:3425505	1	13573	2019-06-30 10:14:05.734452	\N	\N	100
region:SouthWestern:2637295	S2	South Western	region	country:UnitedKingdom:2635167	0	3146	2019-06-30 18:33:38.539754	\N	\N	100
state:Brakna:2380635	S2	Brakna	state	country:Mauritania:2378080	1	3608	2019-07-01 20:58:33.10636	\N	\N	100
fjord:Skagerrak:2960845	S2	Skagerrak	fjord	root	1	8402	2019-06-30 21:10:23.150507	\N	\N	100
state:SaintJohn:7267903	S2	Saint John	state	country:UnitedStatesVirginIslands:4796751	1	506	2019-06-30 10:14:05.231814	\N	\N	100
state:BritishVirginIslands:3577718	S2	British Virgin Islands	state	country:BritishVirginIslands:3577718	1	508	2019-06-30 10:14:05.232566	\N	\N	100
country:BritishVirginIslands:3577718	S2	British Virgin Islands	country	continent:NorthAmerica:6255149	0	508	2019-06-30 10:14:05.232936	\N	\N	100
fjord:Kangertittivaq:3420001	S2	Kangertittivaq	fjord	root	1	4738	2019-06-30 21:37:56.178644	\N	\N	100
state:Ain:3038422	S2	Ain	state	region:RhoneAlpes:11071625	1	1032	2019-07-02 19:15:57.186222	\N	\N	100
bay:BayInutil	S2	Bay Inútil	bay	root	1	2826	2019-07-04 22:20:15.784514	\N	\N	100
state:KunaYala:3701537	S2	Kuna Yala	state	country:Panama:3703430	1	1507	2019-06-30 10:15:25.302042	\N	\N	100
state:Embera:7303686	S2	Emberá	state	country:Panama:3703430	1	2514	2019-06-30 10:15:24.395368	\N	\N	100
state:Darien:3711671	S2	Darién	state	country:Panama:3703430	1	2582	2019-06-30 10:15:28.556892	\N	\N	100
state:Alberta:5883102	S2	Alberta	state	region:WesternCanada:5953214	1	112039	2019-06-30 10:17:02.652777	\N	\N	100
state:DoukkalaAbda:2597549	S2	Doukkala - Abda	state	country:Morocco:2542007	1	3676	2019-07-01 20:57:16.456942	\N	\N	100
state:Colon:3712073	S2	Colón	state	country:Panama:3703430	1	2844	2019-06-30 10:15:54.862211	\N	\N	100
state:BorsodAbaujZemplen:722064	S2	Borsod-Abaúj-Zemplén	state	region:NorthernHungary:	1	2008	2019-07-01 18:56:09.255715	\N	\N	100
region:NorthernHungary:	S2	Northern Hungary	region	country:Hungary:719819	0	3563	2019-07-01 18:56:09.25617	\N	\N	100
state:DependenciasFederales:3640846	S2	Dependencias Federales	state	country:Venezuela:3625428	1	1507	2019-06-30 10:10:38.61803	\N	\N	100
state:Cusco:3941583	S2	Cusco	state	country:Peru:3932488	1	8676	2019-06-30 10:14:47.066811	\N	\N	100
state:Diourbel:2252308	S2	Diourbel	state	country:Senegal:2245662	1	2083	2019-07-02 20:22:09.630772	\N	\N	100
state:Fatick:2251910	S2	Fatick	state	country:Senegal:2245662	1	2443	2019-07-02 20:11:50.634417	\N	\N	100
state:Arequipa:3947319	S2	Arequipa	state	country:Peru:3932488	1	7613	2019-06-30 10:15:00.43114	\N	\N	100
state:SudBandama:2597329	S2	Sud-Bandama	state	country:IvoryCoast:2287781	1	2597	2019-07-02 19:41:41.344906	\N	\N	100
state:ChukchiAutonomousOkrug:2126099	S2	Chukchi Autonomous Okrug	state	region:FarEastern:11961349	1	158373	2019-06-30 10:14:20.330165	\N	\N	100
state:FalklandIslands:3474414	S2	Falkland Islands	state	country:FalklandIslands:3474414	1	5188	2019-06-30 09:52:54.535827	\N	\N	100
country:FalklandIslands:3474414	S2	Falkland Islands	country	continent:SouthAmerica:6255150	0	5188	2019-06-30 09:52:54.53626	\N	\N	100
country:UnitedStatesVirginIslands:4796751	S2	United States Virgin Islands	country	continent:NorthAmerica:6255149	0	2019	2019-06-30 10:14:05.232187	\N	\N	100
state:SaintThomas:7267904	S2	Saint Thomas	state	country:UnitedStatesVirginIslands:4796751	1	1011	2019-06-30 10:14:05.231385	\N	\N	100
state:Cocle:3712162	S2	Coclé	state	country:Panama:3703430	1	852	2019-06-30 10:15:23.272445	\N	\N	100
state:Panama:3703433	S2	Panama	state	country:Panama:3703430	1	3051	2019-06-30 10:15:20.866224	\N	\N	100
bay:MecklenburgerBucht:2872569	S2	Mecklenburger Bucht	bay	root	1	2042	2019-07-02 01:24:48.004089	\N	\N	100
state:Doboj:3294894	S2	Doboj	state	region:RepuplikaSrpska:	1	1547	2019-07-02 17:49:02.616142	\N	\N	100
state:Quiche:3590964	S2	Quiché	state	country:Guatemala:3595528	1	3103	2019-06-30 10:15:33.306744	\N	\N	100
state:AltaVerapaz:3599773	S2	Alta Verapaz	state	country:Guatemala:3595528	1	1062	2019-06-30 10:15:33.307398	\N	\N	100
state:Guanajuato:4005267	S2	Guanajuato	state	country:Mexico:3996063	1	4128	2019-06-30 10:16:20.637637	\N	\N	100
gulf:GolfoDeCalifornia:8552877	S2	Golfo de California	gulf	root	1	21194	2019-06-30 10:15:46.399399	\N	\N	100
state:Queretaro:3520914	S2	Querétaro	state	country:Mexico:3996063	1	3109	2019-06-30 10:16:56.076396	\N	\N	100
state:Tamaulipas:3516391	S2	Tamaulipas	state	country:Mexico:3996063	1	8886	2019-06-30 10:16:09.38494	\N	\N	100
state:Hessen:2905330	S2	Hessen	state	country:Germany:2921044	1	4192	2019-07-02 01:39:25.764008	\N	\N	100
state:Vargas:3830309	S2	Vargas	state	country:Venezuela:3625428	1	1514	2019-06-30 10:10:39.338114	\N	\N	100
state:Missouri:4398678	S2	Missouri	state	region:Midwest:11887750	1	21745	2019-06-30 10:17:06.317386	\N	\N	100
sea:LincolnSea:6054280	S2	Lincoln Sea	sea	root	1	15009	2019-06-30 10:16:36.658254	\N	\N	100
state:Granma:3558052	S2	Granma	state	country:Cuba:3562981	1	1582	2019-06-30 10:15:26.001382	\N	\N	100
state:Aragua:3649110	S2	Aragua	state	country:Venezuela:3625428	1	2048	2019-06-30 10:10:39.338738	\N	\N	100
country:Cuba:3562981	S2	Cuba	country	continent:NorthAmerica:6255149	0	15785	2019-06-30 10:15:26.002111	\N	\N	100
state:RailikChain:	S2	Railik Chain	state	country:MarshallIslands:2080185	1	1366	2019-06-30 10:10:23.565645	\N	\N	100
state:SanLuisPotosi:3985605	S2	San Luis Potosí	state	country:Mexico:3996063	1	8954	2019-06-30 10:16:12.871563	\N	\N	100
country:Bolivia:3923057	S2	Bolivia	country	continent:SouthAmerica:6255150	0	103962	2019-06-30 10:10:25.781303	\N	\N	100
state:Zacatecas:3979840	S2	Zacatecas	state	country:Mexico:3996063	1	11877	2019-06-30 10:16:12.870912	\N	\N	100
state:SantaCruz:3904907	S2	Santa Cruz	state	country:Bolivia:3923057	1	34469	2019-06-30 10:10:25.780853	\N	\N	100
strait:HudsonStrait:5978191	S2	Hudson Strait	strait	root	1	42342	2019-06-30 10:15:00.075675	\N	\N	100
state:Texas:4736286	S2	Texas	state	region:South:11887752	1	72272	2019-06-30 10:16:02.265871	\N	\N	100
state:MadreDeDios:3935619	S2	Madre de Dios	state	country:Peru:3932488	1	9413	2019-06-30 10:15:06.274306	\N	\N	100
state:Vilniaus:864485	S2	Vilniaus	state	country:Lithuania:597427	1	2540	2019-07-01 18:51:40.680763	\N	\N	100
state:BrongAhafo:2302547	S2	Brong Ahafo	state	country:Ghana:2300660	1	4699	2019-06-30 09:51:45.698591	\N	\N	100
state:BasRhin:3034720	S2	Bas-Rhin	state	region:Alsace:11071622	1	2565	2019-07-02 19:04:14.415645	\N	\N	100
state:PresidenteHayes:3437443	S2	Presidente Hayes	state	country:Paraguay:3437598	1	11944	2019-06-30 10:10:32.476862	\N	\N	100
region:Alsace:11071622	S2	Alsace	region	country:France:3017382	0	1573	2019-07-02 19:16:00.381837	\N	\N	100
bay:FrobisherBay:5983720	S2	Frobisher Bay	bay	root	1	4843	2019-06-30 10:15:11.783405	\N	\N	100
state:Mopti:2453347	S2	Mopti	state	country:Mali:2453866	1	9535	2019-06-30 09:51:46.845926	\N	\N	100
state:Sourou:2355211	S2	Sourou	state	region:BoucleDuMouhoun:6930701	1	1053	2019-06-30 09:51:46.84392	\N	\N	100
region:BoucleDuMouhoun:6930701	S2	Boucle du Mouhoun	region	country:BurkinaFaso:2361809	0	4209	2019-06-30 09:51:46.687645	\N	\N	100
state:Indiana:4921868	S2	Indiana	state	region:Midwest:11887750	1	9762	2019-06-30 10:17:03.926179	\N	\N	100
state:Coahuila:4013674	S2	Coahuila	state	country:Mexico:3996063	1	20085	2019-06-30 10:16:19.340089	\N	\N	100
state:LosSantos:3704961	S2	Los Santos	state	country:Panama:3703430	1	1012	2019-06-30 10:15:17.314194	\N	\N	100
country:Panama:3703430	S2	Panama	country	continent:NorthAmerica:6255149	0	13104	2019-06-30 10:15:17.314799	\N	\N	100
sea:BalticSea:2633321	S2	Baltic Sea	sea	root	1	42750	2019-06-30 09:52:01.500036	\N	\N	100
state:Kentucky:6254925	S2	Kentucky	state	region:South:11887752	1	11923	2019-06-30 10:15:53.015621	\N	\N	100
sound:Oresund:6355141	S2	Øresund	sound	root	1	1028	2019-06-30 09:52:01.861311	\N	\N	100
state:Hovedstaden:6418538	S2	Hovedstaden	state	country:Denmark:2623032	1	5061	2019-06-30 09:52:01.866258	\N	\N	100
state:Sjaaelland:6418541	S2	Sjaælland	state	country:Denmark:2623032	1	3085	2019-06-30 09:52:01.867491	\N	\N	100
state:MSila:2486682	S2	M'Sila	state	country:Algeria:2589581	1	3556	2019-06-30 09:51:53.435766	\N	\N	100
state:Biskra:2503822	S2	Biskra	state	country:Algeria:2589581	1	3548	2019-07-02 00:19:34.200588	\N	\N	100
state:Vestfirdir:3426185	S2	Vestfirðir	state	country:Iceland:2629691	1	5432	2019-06-30 21:37:15.825805	\N	\N	100
fjord:UummannaqFjord:3418843	S2	Uummannaq Fjord	fjord	root	1	7288	2019-06-30 10:14:36.557372	\N	\N	100
state:SudOuest:2221788	S2	Sud-Ouest	state	country:Cameroon:2233387	1	3053	2019-07-01 19:15:53.096356	\N	\N	100
state:Cayo:3582429	S2	Cayo	state	country:Belize:3582678	1	2059	2019-06-30 10:16:06.722275	\N	\N	100
state:OrangeWalk:3581511	S2	Orange Walk	state	country:Belize:3582678	1	1034	2019-06-30 10:16:39.35296	\N	\N	100
country:Cameroon:2233387	S2	Cameroon	country	continent:Africa:6255146	0	47500	2019-06-30 18:21:16.89606	\N	\N	100
state:Orense:3114964	S2	Orense	state	region:Galicia:3336902	1	2021	2019-07-02 19:29:23.24381	\N	\N	100
state:Braganca:2742026	S2	Bragança	state	region:Norte:3372843	1	1475	2019-07-01 19:31:46.514831	\N	\N	100
state:Belize:3582676	S2	Belize	state	country:Belize:3582678	1	1564	2019-06-30 10:16:21.307376	\N	\N	100
country:Belize:3582678	S2	Belize	country	continent:NorthAmerica:6255149	0	4186	2019-06-30 10:15:27.670689	\N	\N	100
state:Bissau:2374776	S2	Bissau	state	region:Bissau:2372248	1	514	2019-07-04 17:50:52.431439	\N	\N	100
region:Bissau:2372248	S2	Bissau	region	country:GuineaBissau:2372248	0	514	2019-07-04 17:50:52.432271	\N	\N	100
state:Quinara:2370360	S2	Quinara	state	region:Sul:2369151	1	514	2019-07-04 17:50:52.432768	\N	\N	100
state:Oio:2371071	S2	Oio	state	region:Norte:2373280	1	524	2019-07-04 17:50:52.433233	\N	\N	100
state:Wyoming:5843591	S2	Wyoming	state	region:West:11887751	1	30468	2019-06-30 10:15:42.385968	\N	\N	100
state:Flintshire:2649298	S2	Flintshire	state	region:WestWalesAndTheValleys:	1	1000	2019-07-02 20:28:25.026703	\N	\N	100
state:Anglesey:2657311	S2	Anglesey	state	region:EastWales:7302126	1	2996	2019-07-02 20:14:59.803957	\N	\N	100
state:Yucatan:3514211	S2	Yucatán	state	country:Mexico:3996063	1	6396	2019-06-30 10:15:51.96719	\N	\N	100
state:Aberdeenshire:2657830	S2	Aberdeenshire	state	region:NorthEastern:	1	2999	2019-06-30 18:32:33.464836	\N	\N	100
region:NorthEastern:	S2	North Eastern	region	country:UnitedKingdom:2635167	0	1828	2022-05-05 16:45:49.719011	\N	\N	100
state:Acre:3665474	S2	Acre	state	country:Brazil:3469034	1	16656	2019-06-30 10:15:10.829414	\N	\N	100
state:GeorgeHill:11205442	S2	George Hill	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.41311	\N	\N	100
state:EastEnd:11205433	S2	East End	state	country:Anguilla:3573512	1	2040	2019-06-30 10:14:10.419322	\N	\N	100
state:NorthHill:11205436	S2	North Hill	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.413655	\N	\N	100
state::	S2	_all	state	country:Anguilla:3573512	1	2651	2019-06-30 10:14:10.414279	\N	\N	100
state:TheValley:3573374	S2	The Valley	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.414809	\N	\N	100
state:TheFarrington:11205444	S2	The Farrington	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.415328	\N	\N	100
state:NorthSide:11205440	S2	North Side	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.415854	\N	\N	100
state:StoneyGround:11205443	S2	Stoney Ground	state	country:Anguilla:3573512	1	509	2019-06-30 10:14:10.416403	\N	\N	100
state:SouthHill:11205438	S2	South Hill	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.41699	\N	\N	100
state:BlowingPoint:3573496	S2	Blowing Point	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.417546	\N	\N	100
state:WestEnd:11205437	S2	West End	state	country:Anguilla:3573512	1	504	2019-06-30 10:14:10.418213	\N	\N	100
state:SandyHill:11205393	S2	Sandy Hill	state	country:Anguilla:3573512	1	738	2019-06-30 10:14:10.418773	\N	\N	100
country:Anguilla:3573512	S2	Anguilla	country	continent:NorthAmerica:6255149	0	525	2019-06-30 10:14:10.419853	\N	\N	100
state:Sipaliwini:3383062	S2	Sipaliwini	state	country:Suriname:3382998	1	10521	2019-06-30 10:10:22.507136	\N	\N	100
state:Guainia:3681652	S2	Guainía	state	country:Colombia:3686110	1	7119	2019-06-30 10:10:40.944321	\N	\N	100
country:Suriname:3382998	S2	Suriname	country	continent:SouthAmerica:6255150	0	12503	2019-06-30 10:10:21.808139	\N	\N	100
state:GuyaneFrancaise:3381670	S2	Guyane française	state	region:GuyaneFrancaise:	1	10771	2019-06-30 10:10:21.758695	\N	\N	100
region:GuyaneFrancaise:	S2	Guyane française	region	country:France:3017382	0	10794	2019-06-30 10:10:21.759092	\N	\N	100
state:HauteSaone:3013737	S2	Haute-Saône	state	region:FrancheComte:11071619	1	1084	2019-07-02 19:17:50.940712	\N	\N	100
state:Bremen:2944387	S2	Bremen	state	country:Germany:2921044	1	2037	2019-06-30 09:51:57.845203	\N	\N	100
state:Niedersachsen:2862926	S2	Niedersachsen	state	country:Germany:2921044	1	10478	2019-06-30 09:51:55.943416	\N	\N	100
bay:BahiaGrande:10212566	S2	Bahía Grande	bay	root	1	2767	2019-06-30 10:14:27.630355	\N	\N	100
state:NewBrunswick:6087430	S2	New Brunswick	state	region:EasternCanada:	1	12677	2019-06-30 10:15:28.854084	\N	\N	100
state:Michoacan:3995955	S2	Michoacán	state	country:Mexico:3996063	1	9321	2019-06-30 10:16:20.636834	\N	\N	100
sea:TyrrhenianSea:2522836	S2	Tyrrhenian Sea	sea	root	1	31622	2019-06-30 17:58:33.624436	\N	\N	100
state:Cosenza:2524906	S2	Cosenza	state	region:Calabria:2525468	1	4141	2019-07-02 17:50:02.88817	\N	\N	100
region:Calabria:2525468	S2	Calabria	region	country:Italy:3175395	0	3360	2019-07-02 17:50:02.888644	\N	\N	100
sea:LaptevSea:2038683	S2	Laptev Sea	sea	root	1	139249	2019-06-30 10:14:57.794393	\N	\N	100
channel:SaintLawrenceRiver:7315779	S2	Saint Lawrence River	channel	root	1	7936	2019-06-30 10:15:23.964111	\N	\N	100
sea:BalearicSea:2363257	S2	Balearic Sea	sea	root	1	12363	2019-06-30 09:51:54.490072	\N	\N	100
state:Ontario:6093943	S2	Ontario	state	region:EasternCanada:	1	171661	2019-06-30 10:15:59.729393	\N	\N	100
state:Moquegua:3934607	S2	Moquegua	state	country:Peru:3932488	1	4118	2019-06-30 10:15:16.621532	\N	\N	100
state:Carmarthenshire:2653753	S2	Carmarthenshire	state	region:EastWales:7302126	1	1002	2019-07-02 20:22:07.161309	\N	\N	100
state:BuenosAires:3435907	S2	Buenos Aires	state	country:Argentina:3865483	1	38165	2019-06-30 10:10:31.71884	\N	\N	100
state:Herefordshire:2647071	S2	Herefordshire	state	region:WestMidlands:11591953	1	663	2019-07-04 18:14:15.117004	\N	\N	100
state:Washington:5815135	S2	Washington	state	region:West:11887751	1	27867	2019-06-30 10:17:06.1528	\N	\N	100
state:SintMaarten:7609695	S2	Sint Maarten	state	country:SintMaarten:7609695	1	1818	2019-06-30 10:14:10.410921	\N	\N	100
country:SintMaarten:7609695	S2	Sint Maarten	country	continent:NorthAmerica:6255149	0	1818	2019-06-30 10:14:10.411514	\N	\N	100
state:St.Martin:3578421	S2	St. Martin	state	country:SaintMartin:3578421	1	1965	2019-06-30 10:14:10.412063	\N	\N	100
country:SaintMartin:3578421	S2	Saint-Martin	country	continent:NorthAmerica:6255149	0	1965	2019-06-30 10:14:10.412592	\N	\N	100
state:Jigawa:2565345	S2	Jigawa	state	country:Nigeria:2328926	1	2830	2019-07-04 15:34:27.071351	\N	\N	100
state:Anzoategui:3649198	S2	Anzoátegui	state	country:Venezuela:3625428	1	7240	2019-06-30 10:10:39.423553	\N	\N	100
\.


--
-- PostgreSQL database dump complete
--

