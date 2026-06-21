-- Default admin account (password: admin123)
INSERT INTO `users` (`role`, `name`, `email`, `password`, `is_active`) VALUES
('admin', 'Admin', 'admin@autimind.com', '$2y$12$Pa0aTBNWo9u9u2ceKpZeL.ptDICC/KWutxY29dWUFob3/B87AeeRK', 1),
('specialist', 'Dr. Sarah Chen', 'sarah@autimind.com', '$2y$12$Pa0aTBNWo9u9u2ceKpZeL.ptDICC/KWutxY29dWUFob3/B87AeeRK', 1),
('specialist', 'David Okonkwo', 'david@autimind.com', '$2y$12$Pa0aTBNWo9u9u2ceKpZeL.ptDICC/KWutxY29dWUFob3/B87AeeRK', 1);

INSERT INTO `specialist_details` (`user_id`, `title`, `bio`, `specializations`, `years_experience`) VALUES
(2, 'Speech Therapist', 'Expert in pediatric speech and language development.', '["speech therapy","language disorders","social communication"]', 12),
(3, 'Behavior Analyst', 'Board-certified behavior analyst specializing in early intervention.', '["ABA therapy","behavior management","early intervention"]', 8);

-- Quiz questions (10 questions, 4 categories)
INSERT INTO `quiz_questions` (`id`, `question_text`, `category`, `order_index`) VALUES
(1, 'Does your child make eye contact when interacting with you?', 'social_communication', 1),
(2, 'Does your child respond to their name when called?', 'social_communication', 2),
(3, 'Does your child engage in pretend play (e.g., feeding a doll)?', 'social_communication', 3),
(4, 'Does your child have repetitive movements (e.g., hand-flapping, rocking)?', 'behavior', 4),
(5, 'Does your child become distressed by minor changes in routine?', 'behavior', 5),
(6, 'Does your child show intense, focused interests in specific objects or topics?', 'behavior', 6),
(7, 'Does your child react strongly to certain sounds, textures, or lights?', 'sensory', 7),
(8, 'Does your child seek out or avoid specific sensory experiences?', 'sensory', 8),
(9, 'Does your child use gestures (pointing, waving) to communicate?', 'developmental', 9),
(10, 'Does your child imitate actions or sounds you make?', 'developmental', 10);

-- Options (6 per question, weight 0-5)
INSERT INTO `quiz_options` (`question_id`, `option_text`, `weight`, `order_index`) VALUES
(1, 'Always', 5, 1), (1, 'Usually', 4, 2), (1, 'Often', 3, 3), (1, 'Sometimes', 2, 4), (1, 'Rarely', 1, 5), (1, 'Never', 0, 6),
(2, 'Always', 5, 1), (2, 'Usually', 4, 2), (2, 'Often', 3, 3), (2, 'Sometimes', 2, 4), (2, 'Rarely', 1, 5), (2, 'Never', 0, 6),
(3, 'Always', 5, 1), (3, 'Usually', 4, 2), (3, 'Often', 3, 3), (3, 'Sometimes', 2, 4), (3, 'Rarely', 1, 5), (3, 'Never', 0, 6),
(4, 'Always', 5, 1), (4, 'Usually', 4, 2), (4, 'Often', 3, 3), (4, 'Sometimes', 2, 4), (4, 'Rarely', 1, 5), (4, 'Never', 0, 6),
(5, 'Always', 5, 1), (5, 'Usually', 4, 2), (5, 'Often', 3, 3), (5, 'Sometimes', 2, 4), (5, 'Rarely', 1, 5), (5, 'Never', 0, 6),
(6, 'Always', 5, 1), (6, 'Usually', 4, 2), (6, 'Often', 3, 3), (6, 'Sometimes', 2, 4), (6, 'Rarely', 1, 5), (6, 'Never', 0, 6),
(7, 'Always', 5, 1), (7, 'Usually', 4, 2), (7, 'Often', 3, 3), (7, 'Sometimes', 2, 4), (7, 'Rarely', 1, 5), (7, 'Never', 0, 6),
(8, 'Always', 5, 1), (8, 'Usually', 4, 2), (8, 'Often', 3, 3), (8, 'Sometimes', 2, 4), (8, 'Rarely', 1, 5), (8, 'Never', 0, 6),
(9, 'Always', 5, 1), (9, 'Usually', 4, 2), (9, 'Often', 3, 3), (9, 'Sometimes', 2, 4), (9, 'Rarely', 1, 5), (9, 'Never', 0, 6),
(10, 'Always', 5, 1), (10, 'Usually', 4, 2), (10, 'Often', 3, 3), (10, 'Sometimes', 2, 4), (10, 'Rarely', 1, 5), (10, 'Never', 0, 6);

-- FAQ items
INSERT INTO `faq_items` (`question`, `answer`, `category`, `order_index`) VALUES
('What is AutiMind?', 'AutiMind is a comprehensive platform designed to support children with autism and their families through early screening, personalized activities, professional guidance, and a supportive community.', 'general', 1),
('Who can use AutiMind?', 'AutiMind is designed for parents, caregivers, educators, and healthcare professionals working with children on the autism spectrum.', 'general', 2),
('How does the screening quiz work?', 'Our screening quiz consists of 10 clinically-informed questions across key developmental areas. Based on your answers, we provide a risk assessment along with personalized recommendations.', 'features', 3),
('Can I track my child''s progress?', 'Yes! AutiMind includes a detailed progress tracking system that monitors your child''s development across activities, quiz results, and behavioral milestones over time.', 'features', 4),
('Is AutiMind free to use?', 'We offer a free Standard plan with basic features. Our Premium and Family plans unlock advanced tools, detailed analytics, and direct specialist messaging.', 'pricing', 5),
('Can I upgrade or downgrade my plan?', 'Yes, you can change your plan at any time. Upgrades take effect immediately, while downgrades apply at the end of your billing cycle.', 'pricing', 6),
('Is my data secure?', 'Absolutely. We use industry-standard encryption, secure servers, and strict data protection protocols. Your family''s privacy is our top priority.', 'technical', 7),
('Which devices are supported?', 'AutiMind works on all modern browsers on desktop, tablet, and mobile devices. Our responsive design ensures a seamless experience across screen sizes.', 'technical', 8),
('How do I contact support?', 'You can reach our support team through the Contact form on our website, via email at autimind@autism.com, or by phone at +91 6232-1151-22.', 'technical', 9);

-- Chatbot responses
INSERT INTO `chatbot_responses` (`keywords`, `response_text`, `category`) VALUES
('["hello","hi","hey","greetings"]', 'Hello! I''m AutiMind assistant. How can I help you today?', 'general'),
('["autism","what is autism","spectrum"]', 'Autism Spectrum Disorder (ASD) is a developmental condition that affects communication, behavior, and social interaction. Every child with autism is unique, and early intervention can make a significant difference.', 'general'),
('["screening","quiz","test","assessment"]', 'Our screening quiz helps identify potential signs of autism. It takes about 10 minutes and covers key developmental areas. You can start it from your Parent Dashboard.', 'features'),
('["progress","tracking","monitor"]', 'Progress tracking allows you to monitor your child''s development across activities and quiz results over time. You can view detailed reports in your Parent Dashboard.', 'features'),
('["appointment","booking","schedule","specialist"]', 'You can browse our specialist directory and book appointments directly from your Parent Dashboard. Simply select a specialist, choose an available time slot, and confirm.', 'features'),
('["message","contact","specialist"]', 'You can send messages to your child''s specialist through the messaging system in your Parent Dashboard.', 'features'),
('["password","forgot","reset","login"]', 'If you forgot your password, click "Forgot Password" on the login page and follow the instructions sent to your email.', 'technical'),
('["pricing","plan","cost","subscription"]', 'We offer three plans: Standard (free), Premium ($19/month), and Family ($29/month). Each plan includes different features. Visit our Pricing page for details.', 'pricing');
