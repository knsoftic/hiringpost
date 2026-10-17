-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 17, 2026 at 10:53 AM
-- Server version: 10.11.19-MariaDB-cll-lve-log
-- PHP Version: 8.4.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hiringpo_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `failed_attempts` tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `failed_attempts`, `locked_until`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$L2sKj1ofKTXBvh18SNuWOO5ClT0WBLdBexa2A6ON.JbwmBPjPihTq', 0, NULL, '2026-10-17 10:39:47', '2026-10-10 14:31:10');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(10) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(191) NOT NULL,
  `excerpt` text DEFAULT NULL,
  `content` mediumtext NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `video_url` varchar(500) DEFAULT NULL,
  `status` enum('draft','published') NOT NULL DEFAULT 'draft',
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(320) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `slug`, `excerpt`, `content`, `featured_image`, `video_url`, `status`, `meta_title`, `meta_description`, `published_at`, `created_at`, `updated_at`) VALUES
(2, 'UK Construction Jobs 2026 | Civil Engineer, Electrician & Plumber Jobs', 'uk-construction-jobs-2026-civil-engineer-electrician-plumber-jobs', 'Explore UK construction jobs for civil engineers, electricians, plumbers and construction workers. Find current opportunities, employers and official UK job links.', '<p># UK Construction Jobs 2026: Civil Engineer, Electrician, Plumber &amp; Construction Opportunities</p><p>The UK construction and infrastructure sector continues to provide career opportunities for skilled professionals across engineering, electrical, plumbing and building trades. If you are looking for **construction jobs in the UK**, there are opportunities ranging from civil engineering and infrastructure projects to electrical installation, plumbing and building maintenance.</p><p>This guide covers some of the most popular **UK construction jobs**, what employers typically look for, where to search for vacancies, and useful official career websites.</p><p>## Current UK Construction Job Opportunities</p><p>We are highlighting four key areas for skilled workers:</p><p>- Civil Engineer<br>- Electrician<br>- Plumber<br>- Construction Plumber</p><p>The exact responsibilities, qualifications, salary and employment conditions will vary depending on the employer, location and level of experience.</p><p>---</p><p>## 1. Civil Engineer Jobs in the UK</p><p>Civil engineers play an important role in the UK\'s infrastructure sector. They can work on roads, bridges, railways, water systems, buildings, energy projects and major infrastructure programmes.</p><p>Typical responsibilities can include:</p><p>- Preparing and reviewing engineering designs<br>- Supporting construction and infrastructure projects<br>- Working with contractors and project teams<br>- Inspecting construction work<br>- Managing technical documentation<br>- Supporting health and safety requirements<br>- Working with highways, bridges, drainage and transportation infrastructure</p><p>AtkinsRéalis currently advertises civil engineering opportunities across different UK locations and sectors, including transportation, water, nuclear, infrastructure and buildings.</p><p>**Search Civil Engineering Jobs:**  <br>[AtkinsRéalis UK Civil Engineering Jobs](https://careers.atkinsrealis.com/en/our-job-areas/engineering/civil-engineering-jobs-uk?utm_source=chatgpt.com)</p><p>You can also explore engineering opportunities at:</p><p>**Arup** – [Arup Careers](https://www.arup.com/careers/?utm_source=chatgpt.com)</p><p>**Balfour Beatty** – [Balfour Beatty Careers](https://www.balfourbeatty.com/careers/careers-at-balfour-beatty?utm_source=chatgpt.com)</p><p>---</p><p>## 2. Electrician Jobs in the UK</p><p>Electricians are required across construction, commercial buildings, industrial facilities, infrastructure projects, hospitals, schools and facilities management.</p><p>Typical electrician responsibilities include:</p><p>- Installing electrical systems<br>- Electrical testing and fault finding<br>- Maintenance and repair<br>- Reading electrical drawings<br>- Installing lighting and power systems<br>- Maintaining electrical equipment<br>- Following UK electrical safety regulations</p><p>Kier currently lists electrician opportunities among its trades and operative roles, while EQUANS also has dedicated electrician and electrical engineering vacancies across the UK.</p><p>**Search Electrician Jobs:**  <br>[Kier Electrician &amp; Trades Jobs](https://jobs.kier.co.uk/trades-operatives/?utm_source=chatgpt.com)</p><p>**EQUANS Electrician Jobs:**  <br>[EQUANS Trades &amp; Maintenance Jobs](https://www.equans.co.uk/jobs/trades-and-maintenance?utm_source=chatgpt.com)</p><p>Electricians should make sure their qualifications and experience meet the requirements of the specific vacancy before applying.</p><p>---</p><p>## 3. Plumber Jobs in the UK</p><p>Plumbers are essential to residential, commercial and industrial construction projects. UK employers recruit plumbers for installation, maintenance, repairs and building services.</p><p>Common responsibilities include:</p><p>- Installing water and drainage systems<br>- Repairing plumbing systems<br>- Heating and hot-water systems<br>- Preventative maintenance<br>- Identifying leaks and faults<br>- Installing pipes and fixtures<br>- Working with construction and facilities teams</p><p>EQUANS currently has a dedicated trades and maintenance section that includes plumbing vacancies, while Mitie also advertises plumber positions and maintenance engineering roles involving plumbing skills.</p><p>**Search Plumbing Jobs:**  <br>[EQUANS Plumbing Jobs](https://www.equans.co.uk/jobs/trades-and-maintenance?utm_source=chatgpt.com)</p><p>**Mitie Careers:**  <br>[Mitie Careers &amp; Jobs](https://careers.mitie.com/?utm_source=chatgpt.com)</p><p>**Kier Plumber Jobs:**  <br>[Kier Careers](https://jobs.kier.co.uk/careers-at-kier?utm_source=chatgpt.com)</p><p>---</p><p>## 4. Construction Plumber Jobs</p><p>Construction plumbers generally work on new-build and large construction projects where plumbing systems need to be installed as part of the building process.</p><p>Depending on the project, duties may include:</p><p>- Installing pipework<br>- Water supply systems<br>- Drainage systems<br>- Heating systems<br>- Sanitary installations<br>- Testing plumbing systems<br>- Reading technical drawings<br>- Working alongside builders, engineers and other trades</p><p>Large construction and facilities companies regularly recruit people with plumbing and multi-trade experience.</p><p>For example, EQUANS lists plumbing and maintenance positions, while Kier\'s trades and operative opportunities include multi-tradesperson roles involving plumbing work.</p><p>---</p><p># Top UK Companies to Check for Construction Jobs</p><p>If you are actively searching for construction employment in the UK, it is a good idea to apply directly through the employer\'s official careers website.</p><p>### AtkinsRéalis</p><p>AtkinsRéalis offers opportunities across civil engineering, transportation, water, nuclear, buildings, energy and infrastructure. Its UK operations cover locations including London, Birmingham, Manchester, Leeds, Glasgow and other areas.</p><p>[View AtkinsRéalis UK Jobs](https://careers.atkinsrealis.com/en/global-locations/uk-europe?utm_source=chatgpt.com)</p><p>### Balfour Beatty</p><p>Balfour Beatty works across construction and infrastructure and publishes current vacancies covering areas such as highways, rail, major projects, regional civils, power and engineering.</p><p>[View Balfour Beatty Jobs](https://www.balfourbeatty.com/careers/job-search?utm_source=chatgpt.com)</p><p>### Arup</p><p>Arup provides career opportunities across engineering, infrastructure, buildings, transport and other technical disciplines.</p><p>[View Arup Careers](https://www.arup.com/careers/?utm_source=chatgpt.com)</p><p>### Kier</p><p>Kier recruits across construction, engineering, trades and operational roles. Its trades section includes electricians, water technicians and multi-tradesperson opportunities.</p><p>[View Kier Careers](https://jobs.kier.co.uk/careers-at-kier?utm_source=chatgpt.com)</p><p>### EQUANS</p><p>EQUANS has UK opportunities across engineering, construction, facilities management and trades. Its job search currently includes categories for electricians, electrical work, plumbing, construction and maintenance.</p><p>[View EQUANS UK Jobs](https://www.equans.co.uk/job-search?utm_source=chatgpt.com)</p><p>### Mitie</p><p>Mitie offers facilities management and engineering opportunities across the UK, including roles involving electrical, mechanical, HVAC, plumbing, drainage and other building services.</p><p>[View Mitie Careers](https://careers.mitie.com/?utm_source=chatgpt.com)</p><p>---</p><p># Who Can Apply for UK Construction Jobs?</p><p>Requirements depend on the position. Employers may look for:</p><p>- Relevant qualifications<br>- Previous construction experience<br>- Trade certification where required<br>- Technical knowledge<br>- Good communication skills<br>- Ability to follow health and safety procedures<br>- Ability to work independently and as part of a team<br>- Right to work in the UK<br>- Relevant professional registration for certain engineering positions</p><p>For experienced candidates, having a strong CV that clearly explains previous projects, qualifications and practical skills can make the application stronger.</p><p>---</p><p># UK Construction Jobs for International Applicants</p><p>International applicants should check the immigration requirements before applying.</p><p>The UK Skilled Worker route requires an eligible occupation, an approved UK employer, a Certificate of Sponsorship and the applicable salary requirements.</p><p>Civil engineering roles are listed under occupation code 2121 and are classified as higher skilled. Electricians and electrical fitters are listed under code 5241, while plumbers and heating/ventilation installers are listed under code 5315.</p><p>However, **being listed as an eligible occupation does not automatically mean that every employer or vacancy provides visa sponsorship**. Candidates should check the individual vacancy and employer sponsorship status before applying.</p><p>You can check the official UK government guidance here:</p><p>[GOV.UK Skilled Worker Visa – Your Job](https://www.gov.uk/skilled-worker-visa/your-job?utm_source=chatgpt.com)</p><p>[GOV.UK Eligible Occupations for Skilled Worker Visa](https://www.gov.uk/government/publications/skilled-worker-visa-eligible-occupations/skilled-worker-visa-eligible-occupations-and-codes?utm_source=chatgpt.com)</p><p>---</p><p># How to Find Construction Jobs in the UK</p><p>A successful UK job search should focus on official employer websites and reputable recruitment platforms.</p><p>### Step 1: Choose Your Job Category</p><p>Decide whether you are applying for:</p><p>- Civil Engineer<br>- Site Engineer<br>- Electrical Engineer<br>- Electrician<br>- Plumber<br>- Construction Plumber<br>- Multi-Skilled Engineer<br>- Maintenance Engineer<br>- Construction Supervisor</p><p>### Step 2: Prepare Your CV</p><p>Your CV should include:</p><p>- Full name and contact details<br>- Professional summary<br>- Work experience<br>- Qualifications<br>- Trade certifications<br>- Key technical skills<br>- Major projects<br>- References where appropriate</p><p>### Step 3: Apply Through Official Websites</p><p>Whenever possible, apply directly through the company\'s official careers website.</p><p>Avoid paying individuals for a job offer or sending money to someone who claims they can guarantee employment.</p><p>Arup specifically warns candidates about recruitment fraud, and Balfour Beatty also warns that fraudulent recruiters may use its brand and that applicants should not be asked for money for employment.</p><p>### Step 4: Check the Job Requirements</p><p>Before applying, carefully check:</p><p>- Location<br>- Salary<br>- Working hours<br>- Contract type<br>- Qualifications<br>- Experience requirements<br>- Right-to-work requirements<br>- Visa sponsorship, if applicable</p><p>---</p><p># Why Consider a Career in UK Construction?</p><p>The construction and infrastructure industry offers opportunities across many regions and specialisms.</p><p>Depending on your skills and experience, you may find opportunities in:</p><p>- London<br>- Birmingham<br>- Manchester<br>- Leeds<br>- Bristol<br>- Glasgow<br>- Edinburgh<br>- Cardiff<br>- Liverpool<br>- Newcastle<br>- Sheffield<br>- Southampton<br>- Other UK locations</p><p>Major infrastructure, housing, transportation, energy, water and commercial projects create demand for professionals across engineering and skilled trades.</p><p>---</p><p># Final Thoughts</p><p>If you are a **Civil Engineer, Electrician, Plumber or Construction Plumber**, the UK construction sector offers a wide range of career paths.</p><p>The best approach is to identify your occupation, prepare a professional UK-style CV, check the qualification requirements and apply directly through trusted employer career pages.</p><p>For international applicants, always verify the current UK immigration rules and confirm whether the specific employer and vacancy can sponsor your application. Do not assume that a job is sponsored simply because the occupation itself may be eligible for a particular visa route.</p><p>Start your search with official career websites such as **AtkinsRéalis, Balfour Beatty, Arup, Kier, EQUANS and Mitie**, and check their latest vacancies regularly.</p><p>**Good luck with your UK construction job search!**</p>', '202610-a889b3f1c44e4d65.png', NULL, 'published', 'UK Construction Jobs 2026 | Civil Engineer, Electrician obs', 'Find the latest UK construction jobs for Civil Engineers, Electricians, Plumbers and Construction Plumbers. Explore UK employers, job opportunities and visa', '2026-10-10 15:22:15', '2026-10-10 15:22:15', '2026-10-10 15:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`setting_key`, `setting_value`) VALUES
('ad_home', ''),
('ad_post', ''),
('ads_enabled', '1'),
('ads_head_code', '<script src=\"https://quge5.com/88/tag.min.js\" data-zone=\"293443\" async data-cfasync=\"false\"></script>'),
('category_name', 'Jobs'),
('footer_text', ''),
('monetag_meta', '8a0ad7542580ae2ed6ca66fe049c2a5e'),
('monetag_sw', ''),
('posts_per_page', '9'),
('site_description', 'Fresh vlogs and practical articles, published regularly.'),
('site_logo', '202610-84fb106b31456384.png'),
('site_tagline', 'Stories, guides and vlogs worth your time'),
('site_title', 'Job Post');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_admins_username` (`username`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_posts_slug` (`slug`),
  ADD KEY `idx_posts_status_published` (`status`,`published_at`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
