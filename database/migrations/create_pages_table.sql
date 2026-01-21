-- Create pages table for editable content pages

CREATE TABLE IF NOT EXISTS pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(100) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    content LONGTEXT NULL,
    meta_description VARCHAR(300) NULL,
    is_active TINYINT(1) DEFAULT 1,
    is_system TINYINT(1) DEFAULT 0,
    show_in_footer TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default legal pages
INSERT INTO pages (slug, title, content, meta_description, is_active, is_system, show_in_footer, sort_order) VALUES
('about', 'About Us', '<h2>About {{site_name}}</h2>
<p>Welcome to {{site_name}}, your destination for discovering and sharing stunning visual content.</p>

<h3>Our Mission</h3>
<p>We believe in the power of imagery to inspire, educate, and connect people around the world. Our platform provides a space for photographers, artists, and visual creators to showcase their work and for audiences to discover amazing content.</p>

<h3>What We Offer</h3>
<ul>
<li><strong>Curated Collections</strong> - Carefully organized galleries featuring the best visual content</li>
<li><strong>Easy Discovery</strong> - Powerful search and categorization to help you find exactly what you''re looking for</li>
<li><strong>Community</strong> - Connect with like-minded individuals who share your passion for visual arts</li>
</ul>

<h3>Contact Us</h3>
<p>Have questions or feedback? We''d love to hear from you. Reach out through our contact page.</p>', 'Learn more about {{site_name}} and our mission to showcase amazing visual content.', 1, 1, 1, 1),

('terms', 'Terms of Service', '<h2>Terms of Service</h2>
<p><em>Last updated: {{current_date}}</em></p>

<h3>1. Acceptance of Terms</h3>
<p>By accessing and using {{site_name}} ("the Service"), you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the Service.</p>

<h3>2. Description of Service</h3>
<p>{{site_name}} provides a platform for users to view, share, and interact with visual content including images and photographs.</p>

<h3>3. User Accounts</h3>
<p>To access certain features, you may need to create an account. You are responsible for:</p>
<ul>
<li>Maintaining the confidentiality of your account credentials</li>
<li>All activities that occur under your account</li>
<li>Notifying us immediately of any unauthorized use</li>
</ul>

<h3>4. User Content</h3>
<p>Users may upload content to the Service. By uploading content, you:</p>
<ul>
<li>Warrant that you own or have the right to share the content</li>
<li>Grant us a license to display and distribute the content on our platform</li>
<li>Agree not to upload illegal, harmful, or infringing content</li>
</ul>

<h3>5. Prohibited Conduct</h3>
<p>You agree not to:</p>
<ul>
<li>Upload content that infringes on intellectual property rights</li>
<li>Harass, abuse, or harm other users</li>
<li>Attempt to gain unauthorized access to the Service</li>
<li>Use the Service for any illegal purpose</li>
<li>Interfere with the proper functioning of the Service</li>
</ul>

<h3>6. Intellectual Property</h3>
<p>The Service and its original content (excluding user-uploaded content) are owned by {{site_name}} and protected by copyright, trademark, and other intellectual property laws.</p>

<h3>7. Termination</h3>
<p>We reserve the right to terminate or suspend your account at any time for violations of these terms or for any other reason at our discretion.</p>

<h3>8. Disclaimer of Warranties</h3>
<p>The Service is provided "as is" without warranties of any kind, either express or implied.</p>

<h3>9. Limitation of Liability</h3>
<p>In no event shall {{site_name}} be liable for any indirect, incidental, special, or consequential damages arising out of your use of the Service.</p>

<h3>10. Changes to Terms</h3>
<p>We reserve the right to modify these terms at any time. Continued use of the Service after changes constitutes acceptance of the new terms.</p>

<h3>11. Contact</h3>
<p>For questions about these Terms, please contact us through our contact page.</p>', 'Terms of Service for {{site_name}}. Read our terms and conditions for using our platform.', 1, 1, 1, 2),

('privacy', 'Privacy Policy', '<h2>Privacy Policy</h2>
<p><em>Last updated: {{current_date}}</em></p>

<h3>1. Introduction</h3>
<p>{{site_name}} ("we", "our", or "us") respects your privacy and is committed to protecting your personal data. This Privacy Policy explains how we collect, use, and safeguard your information.</p>

<h3>2. Information We Collect</h3>
<h4>Information you provide:</h4>
<ul>
<li>Account information (email, username, password)</li>
<li>Profile information you choose to add</li>
<li>Content you upload</li>
<li>Communications with us</li>
</ul>

<h4>Information collected automatically:</h4>
<ul>
<li>IP address and device information</li>
<li>Browser type and settings</li>
<li>Usage data and browsing patterns</li>
<li>Cookies and similar technologies</li>
</ul>

<h3>3. How We Use Your Information</h3>
<p>We use your information to:</p>
<ul>
<li>Provide and maintain the Service</li>
<li>Process your account registration</li>
<li>Respond to your requests and communications</li>
<li>Improve and personalize your experience</li>
<li>Send important notices and updates</li>
<li>Detect and prevent fraud or abuse</li>
</ul>

<h3>4. Information Sharing</h3>
<p>We do not sell your personal information. We may share information with:</p>
<ul>
<li>Service providers who assist in operating our platform</li>
<li>Law enforcement when required by law</li>
<li>Other parties with your consent</li>
</ul>

<h3>5. Data Security</h3>
<p>We implement appropriate security measures to protect your information. However, no method of transmission over the Internet is 100% secure.</p>

<h3>6. Your Rights</h3>
<p>Depending on your location, you may have rights to:</p>
<ul>
<li>Access your personal data</li>
<li>Correct inaccurate data</li>
<li>Delete your data</li>
<li>Object to processing</li>
<li>Data portability</li>
</ul>

<h3>7. Cookies</h3>
<p>We use cookies to enhance your experience. See our Cookie Policy for more details.</p>

<h3>8. Children''s Privacy</h3>
<p>Our Service is not intended for children under 13. We do not knowingly collect information from children under 13.</p>

<h3>9. Changes to This Policy</h3>
<p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new policy on this page.</p>

<h3>10. Contact Us</h3>
<p>If you have questions about this Privacy Policy, please contact us through our contact page.</p>', 'Privacy Policy for {{site_name}}. Learn how we collect, use, and protect your personal information.', 1, 1, 1, 3),

('dmca', 'DMCA Policy', '<h2>DMCA Copyright Policy</h2>
<p><em>Last updated: {{current_date}}</em></p>

<h3>1. Introduction</h3>
<p>{{site_name}} respects the intellectual property rights of others and expects users to do the same. We will respond to notices of alleged copyright infringement that comply with the Digital Millennium Copyright Act (DMCA).</p>

<h3>2. Filing a DMCA Notice</h3>
<p>If you believe that content on our Service infringes your copyright, please provide our designated agent with the following information:</p>
<ol>
<li>A physical or electronic signature of the copyright owner or authorized agent</li>
<li>Identification of the copyrighted work claimed to be infringed</li>
<li>Identification of the material that is claimed to be infringing, including its location on our Service</li>
<li>Your contact information (address, telephone number, email)</li>
<li>A statement that you have a good faith belief that the use is not authorized by the copyright owner</li>
<li>A statement, under penalty of perjury, that the information in the notice is accurate and that you are authorized to act on behalf of the copyright owner</li>
</ol>

<h3>3. Counter-Notification</h3>
<p>If you believe your content was removed in error, you may file a counter-notification containing:</p>
<ol>
<li>Your physical or electronic signature</li>
<li>Identification of the material that was removed and its location before removal</li>
<li>A statement under penalty of perjury that you have a good faith belief the material was removed by mistake</li>
<li>Your name, address, telephone number, and consent to jurisdiction</li>
</ol>

<h3>4. Repeat Infringers</h3>
<p>We will terminate the accounts of users who are repeat infringers.</p>

<h3>5. Contact</h3>
<p>Send DMCA notices to our designated agent through our contact page with "DMCA Notice" in the subject line.</p>', 'DMCA Copyright Policy for {{site_name}}. Learn how to report copyright infringement.', 1, 1, 1, 4),

('cookies', 'Cookie Policy', '<h2>Cookie Policy</h2>
<p><em>Last updated: {{current_date}}</em></p>

<h3>1. What Are Cookies</h3>
<p>Cookies are small text files stored on your device when you visit a website. They help the website remember your preferences and improve your experience.</p>

<h3>2. How We Use Cookies</h3>
<p>{{site_name}} uses cookies for:</p>

<h4>Essential Cookies</h4>
<p>Required for the website to function properly:</p>
<ul>
<li>Session management and authentication</li>
<li>Security features</li>
<li>Remembering your preferences</li>
</ul>

<h4>Analytics Cookies</h4>
<p>Help us understand how visitors use our site:</p>
<ul>
<li>Page views and navigation patterns</li>
<li>Traffic sources</li>
<li>Performance monitoring</li>
</ul>

<h4>Functionality Cookies</h4>
<p>Enable enhanced features:</p>
<ul>
<li>Remembering your settings</li>
<li>Personalized content</li>
<li>Social media integration</li>
</ul>

<h3>3. Third-Party Cookies</h3>
<p>We may use third-party services that set their own cookies, including:</p>
<ul>
<li>Google Analytics for traffic analysis</li>
<li>Social media platforms for sharing features</li>
<li>Advertising partners (if applicable)</li>
</ul>

<h3>4. Managing Cookies</h3>
<p>You can control cookies through your browser settings:</p>
<ul>
<li>Block all cookies</li>
<li>Delete existing cookies</li>
<li>Allow cookies from specific sites</li>
</ul>
<p>Note: Disabling cookies may affect website functionality.</p>

<h3>5. Changes to This Policy</h3>
<p>We may update this Cookie Policy from time to time. Please check back regularly for updates.</p>

<h3>6. Contact</h3>
<p>For questions about our use of cookies, please contact us through our contact page.</p>', 'Cookie Policy for {{site_name}}. Learn how we use cookies to improve your experience.', 1, 1, 1, 5),

('disclaimer', 'Disclaimer', '<h2>Disclaimer</h2>
<p><em>Last updated: {{current_date}}</em></p>

<h3>1. General Information</h3>
<p>The information and content provided on {{site_name}} is for general informational and entertainment purposes only. We make no representations or warranties of any kind, express or implied, about the completeness, accuracy, reliability, or suitability of the content.</p>

<h3>2. User-Generated Content</h3>
<p>{{site_name}} allows users to upload and share images. We do not:</p>
<ul>
<li>Claim ownership of user-uploaded content</li>
<li>Guarantee the accuracy or legality of user content</li>
<li>Endorse opinions expressed in user content</li>
<li>Verify the identity of uploaders</li>
</ul>

<h3>3. External Links</h3>
<p>Our Service may contain links to external websites. We are not responsible for the content or practices of these third-party sites.</p>

<h3>4. No Professional Advice</h3>
<p>Content on this site does not constitute professional advice of any kind. Always seek appropriate professional guidance for specific situations.</p>

<h3>5. Limitation of Liability</h3>
<p>To the fullest extent permitted by law, {{site_name}} shall not be liable for any:</p>
<ul>
<li>Direct, indirect, or consequential damages</li>
<li>Loss of data or profits</li>
<li>Business interruption</li>
<li>Personal injury or property damage</li>
</ul>

<h3>6. Indemnification</h3>
<p>You agree to indemnify and hold harmless {{site_name}} from any claims arising from your use of the Service or violation of these terms.</p>

<h3>7. Changes</h3>
<p>This disclaimer may be updated at any time without notice. Please review it periodically.</p>', 'Legal disclaimer for {{site_name}}. Important information about our liability and user responsibilities.', 1, 1, 1, 6),

('contact', 'Contact Us', '<h2>Contact Us</h2>
<p>We''d love to hear from you! Whether you have a question, feedback, or just want to say hello, feel free to reach out.</p>

<h3>Get in Touch</h3>
<p>The best way to contact us is through email. We typically respond within 24-48 hours.</p>

<h3>Before Contacting Us</h3>
<p>Please check our other pages for answers to common questions:</p>
<ul>
<li><a href="/terms">Terms of Service</a> - Usage rules and guidelines</li>
<li><a href="/privacy">Privacy Policy</a> - How we handle your data</li>
<li><a href="/dmca">DMCA Policy</a> - Copyright infringement reports</li>
<li><a href="/cookies">Cookie Policy</a> - How we use cookies</li>
</ul>

<h3>Report Issues</h3>
<p>To report inappropriate content or technical issues, please include:</p>
<ul>
<li>A clear description of the issue</li>
<li>The URL of the page (if applicable)</li>
<li>Screenshots if relevant</li>
</ul>

<p>Thank you for being part of our community!</p>', 'Contact {{site_name}}. Get in touch with us for questions, feedback, or support.', 1, 1, 1, 7);
