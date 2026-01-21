/**
 * Ad Blocker Detection & Response System
 */
(function() {
    'use strict';

    const AdBlockDetector = {
        detected: false,
        checkCount: 0,
        maxChecks: 3,

        init: function() {
            // Skip for premium users
            if (window.USER_IS_PREMIUM === true) {
                return;
            }

            // Multiple detection methods for accuracy
            this.runDetection();
        },

        runDetection: function() {
            const self = this;
            let detected = false;

            // Method 1: Bait element detection
            const bait = document.createElement('div');
            bait.className = 'ad ads adsbox ad-placement pub_300x250 pub_300x250m pub_728x90 text-ad textAd text_ad text_ads text-ads text-ad-links ad-text adSense adBlock';
            bait.setAttribute('id', 'ad-test-' + Math.random().toString(36).substr(2, 9));
            bait.style.cssText = 'position:absolute;top:-10px;left:-10px;width:1px;height:1px;';
            bait.innerHTML = '&nbsp;';
            document.body.appendChild(bait);

            // Check after brief delay
            setTimeout(function() {
                if (bait) {
                    const computed = window.getComputedStyle(bait);
                    if (computed.display === 'none' ||
                        computed.visibility === 'hidden' ||
                        bait.offsetHeight === 0 ||
                        bait.offsetParent === null) {
                        detected = true;
                    }
                    bait.remove();
                }

                // Method 2: Check if ad script failed to load
                if (!detected) {
                    detected = self.checkAdScript();
                }

                // Method 3: Check blocked resources
                if (!detected) {
                    detected = self.checkBlockedResources();
                }

                if (detected) {
                    self.detected = true;
                    self.handleAdBlockDetected();
                }
            }, 100);
        },

        checkAdScript: function() {
            // Check if our ad container exists and has content
            const adContainers = document.querySelectorAll('[data-ad-slot]');
            for (let container of adContainers) {
                if (container.offsetHeight < 10 || container.children.length === 0) {
                    return true;
                }
            }
            return false;
        },

        checkBlockedResources: function() {
            // Check if common ad-related elements are blocked
            const testUrls = [
                '/assets/js/ads.js', // Our decoy file
            ];

            // This will be checked via the ads.js file callback
            return window.ADS_BLOCKED === true;
        },

        handleAdBlockDetected: function() {
            // Don't show if user dismissed recently (24 hours)
            const dismissed = localStorage.getItem('adblock_dismissed');
            if (dismissed && Date.now() - parseInt(dismissed) < 86400000) {
                return;
            }

            // Don't show on first visit - be polite
            const visitCount = parseInt(localStorage.getItem('visit_count') || '0') + 1;
            localStorage.setItem('visit_count', visitCount);

            if (visitCount < 2) {
                return;
            }

            this.showModal();
        },

        showModal: function() {
            // Create overlay
            const overlay = document.createElement('div');
            overlay.id = 'adblock-overlay';
            overlay.innerHTML = `
                <div class="adblock-modal">
                    <div class="adblock-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                    </div>
                    <h2>Ad Blocker Detected</h2>
                    <p>We rely on ads to keep this site free. Please consider supporting us:</p>

                    <div class="adblock-options">
                        <div class="adblock-option">
                            <div class="option-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                </svg>
                            </div>
                            <h3>Whitelist Us</h3>
                            <p>Disable ad blocker for this site</p>
                            <button class="btn-whitelist" onclick="AdBlockDetector.showWhitelistGuide()">How to Whitelist</button>
                        </div>

                        <div class="adblock-option featured">
                            <div class="badge">Best Value</div>
                            <div class="option-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                                </svg>
                            </div>
                            <h3>Go Premium</h3>
                            <p>Ad-free experience for just</p>
                            <div class="price">₹99<span>/year</span></div>
                            <a href="/premium" class="btn-premium">Subscribe Now</a>
                        </div>

                        <div class="adblock-option">
                            <div class="option-icon">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                            </div>
                            <h3>Free Account</h3>
                            <p>Register to continue with ads</p>
                            <a href="/register" class="btn-register">Create Account</a>
                        </div>
                    </div>

                    <button class="btn-close" onclick="AdBlockDetector.dismissModal()">
                        Continue with limited access
                    </button>
                </div>
            `;

            // Add styles
            const styles = document.createElement('style');
            styles.textContent = `
                #adblock-overlay {
                    position: fixed;
                    inset: 0;
                    background: rgba(0, 0, 0, 0.85);
                    backdrop-filter: blur(8px);
                    z-index: 99999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                    animation: fadeIn 0.3s ease;
                }
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                .adblock-modal {
                    background: #fff;
                    border-radius: 16px;
                    padding: 40px;
                    max-width: 800px;
                    width: 100%;
                    text-align: center;
                    animation: slideUp 0.3s ease;
                }
                @keyframes slideUp {
                    from { transform: translateY(20px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
                .dark .adblock-modal {
                    background: #1f2937;
                    color: #fff;
                }
                .adblock-icon {
                    color: #f59e0b;
                    margin-bottom: 16px;
                }
                .adblock-modal h2 {
                    font-size: 24px;
                    font-weight: 700;
                    margin-bottom: 8px;
                    color: #111;
                }
                .dark .adblock-modal h2 {
                    color: #fff;
                }
                .adblock-modal > p {
                    color: #6b7280;
                    margin-bottom: 32px;
                }
                .adblock-options {
                    display: grid;
                    grid-template-columns: repeat(3, 1fr);
                    gap: 20px;
                    margin-bottom: 24px;
                }
                @media (max-width: 768px) {
                    .adblock-options {
                        grid-template-columns: 1fr;
                    }
                }
                .adblock-option {
                    position: relative;
                    padding: 24px;
                    border: 2px solid #e5e7eb;
                    border-radius: 12px;
                    transition: all 0.2s;
                }
                .dark .adblock-option {
                    border-color: #374151;
                }
                .adblock-option:hover {
                    border-color: #3b82f6;
                }
                .adblock-option.featured {
                    border-color: #3b82f6;
                    background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
                }
                .dark .adblock-option.featured {
                    background: linear-gradient(135deg, #1e3a5f 0%, #1e293b 100%);
                }
                .adblock-option .badge {
                    position: absolute;
                    top: -12px;
                    left: 50%;
                    transform: translateX(-50%);
                    background: #3b82f6;
                    color: #fff;
                    font-size: 12px;
                    font-weight: 600;
                    padding: 4px 12px;
                    border-radius: 20px;
                }
                .option-icon {
                    width: 48px;
                    height: 48px;
                    background: #f3f4f6;
                    border-radius: 12px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 16px;
                    color: #3b82f6;
                }
                .dark .option-icon {
                    background: #374151;
                }
                .adblock-option h3 {
                    font-size: 18px;
                    font-weight: 600;
                    margin-bottom: 8px;
                    color: #111;
                }
                .dark .adblock-option h3 {
                    color: #fff;
                }
                .adblock-option p {
                    font-size: 14px;
                    color: #6b7280;
                    margin-bottom: 16px;
                }
                .price {
                    font-size: 32px;
                    font-weight: 700;
                    color: #3b82f6;
                    margin-bottom: 16px;
                }
                .price span {
                    font-size: 16px;
                    font-weight: 400;
                    color: #6b7280;
                }
                .btn-whitelist, .btn-premium, .btn-register {
                    display: inline-block;
                    padding: 10px 24px;
                    border-radius: 8px;
                    font-weight: 600;
                    font-size: 14px;
                    text-decoration: none;
                    transition: all 0.2s;
                    cursor: pointer;
                    border: none;
                }
                .btn-whitelist {
                    background: #f3f4f6;
                    color: #374151;
                }
                .btn-whitelist:hover {
                    background: #e5e7eb;
                }
                .btn-premium {
                    background: #3b82f6;
                    color: #fff;
                }
                .btn-premium:hover {
                    background: #2563eb;
                }
                .btn-register {
                    background: #10b981;
                    color: #fff;
                }
                .btn-register:hover {
                    background: #059669;
                }
                .btn-close {
                    background: none;
                    border: none;
                    color: #9ca3af;
                    font-size: 14px;
                    cursor: pointer;
                    padding: 8px 16px;
                    transition: color 0.2s;
                }
                .btn-close:hover {
                    color: #6b7280;
                }

                /* Whitelist Guide Modal */
                .whitelist-guide {
                    text-align: left;
                    margin-top: 20px;
                    padding: 20px;
                    background: #f9fafb;
                    border-radius: 8px;
                }
                .dark .whitelist-guide {
                    background: #111827;
                }
                .whitelist-guide h4 {
                    font-weight: 600;
                    margin-bottom: 12px;
                }
                .whitelist-guide ul {
                    list-style: decimal;
                    padding-left: 20px;
                }
                .whitelist-guide li {
                    margin-bottom: 8px;
                    color: #4b5563;
                }
                .dark .whitelist-guide li {
                    color: #9ca3af;
                }
            `;

            document.head.appendChild(styles);
            document.body.appendChild(overlay);
            document.body.style.overflow = 'hidden';
        },

        showWhitelistGuide: function() {
            const container = document.querySelector('.adblock-options');
            if (!container) return;

            // Detect ad blocker type
            const isUblock = navigator.userAgent.includes('uBlock');
            const guide = document.createElement('div');
            guide.className = 'whitelist-guide';
            guide.innerHTML = `
                <h4>How to Whitelist ${window.location.hostname}</h4>
                <ul>
                    <li>Click on your ad blocker icon in the browser toolbar</li>
                    <li>Look for "Don't run on this page" or "Disable" option</li>
                    <li>Click to whitelist ${window.location.hostname}</li>
                    <li>Refresh the page</li>
                </ul>
                <p style="margin-top: 12px; font-size: 13px; color: #6b7280;">
                    Using uBlock Origin? Click the big power button to disable for this site.
                </p>
            `;

            // Remove existing guide if any
            const existing = document.querySelector('.whitelist-guide');
            if (existing) {
                existing.remove();
                return;
            }

            container.after(guide);
        },

        dismissModal: function() {
            const overlay = document.getElementById('adblock-overlay');
            if (overlay) {
                overlay.style.animation = 'fadeOut 0.2s ease forwards';
                setTimeout(() => {
                    overlay.remove();
                    document.body.style.overflow = '';
                }, 200);
            }

            // Remember dismissal for 24 hours
            localStorage.setItem('adblock_dismissed', Date.now().toString());

            // Apply limited access restrictions
            this.applyLimitedAccess();
        },

        applyLimitedAccess: function() {
            // Blur some images after first few
            const images = document.querySelectorAll('.gallery-grid a');
            images.forEach((img, index) => {
                if (index > 5) {
                    img.style.filter = 'blur(8px)';
                    img.style.pointerEvents = 'none';
                    img.setAttribute('title', 'Disable ad blocker or subscribe to view');
                }
            });

            // Add overlay message
            const grid = document.querySelector('.gallery-grid');
            if (grid && images.length > 6) {
                const notice = document.createElement('div');
                notice.style.cssText = 'grid-column: 1/-1; text-align: center; padding: 40px; background: #f3f4f6; border-radius: 8px; margin-top: 20px;';
                notice.innerHTML = `
                    <p style="color: #6b7280; margin-bottom: 16px;">
                        <strong>Limited Access:</strong> Disable ad blocker or <a href="/premium" style="color: #3b82f6;">subscribe for ₹99/year</a> to view all content.
                    </p>
                `;
                grid.appendChild(notice);
            }
        }
    };

    // Add fadeOut animation
    const fadeOutStyle = document.createElement('style');
    fadeOutStyle.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    `;
    document.head.appendChild(fadeOutStyle);

    // Make globally accessible
    window.AdBlockDetector = AdBlockDetector;

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => AdBlockDetector.init());
    } else {
        AdBlockDetector.init();
    }
})();
