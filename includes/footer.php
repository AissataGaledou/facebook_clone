</div><!-- End content-container -->
    
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-links">
                <ul>
                    <li><a href="about.php">About</a></li>
                    <li><a href="privacy.php">Privacy</a></li>
                    <li><a href="terms.php">Terms</a></li>
                    <li><a href="cookies.php">Cookies</a></li>
                    <li><a href="careers.php">Careers</a></li>
                    <li><a href="help.php">Help</a></li>
                </ul>
            </div>
            <div class="copyright">
                <p>&copy; <?php echo date('Y'); ?> Facebook Clone. All rights reserved.</p>
            </div>
            <div class="language-selector">
                <select>
                    <option value="en">English</option>
                    <option value="es">Español</option>
                    <option value="fr">Français</option>
                    <option value="de">Deutsch</option>
                </select>
            </div>
        </div>
    </footer>
    
    <?php if (isset($logged_in) && $logged_in): ?>
    <!-- JavaScript for notifications -->
    <script>
        // This would be replaced with real notification checking
        function checkNotifications() {
            // Ajax request to check for new notifications
            // Update notification counts if needed
        }
        
        // Check for new notifications every 30 seconds
        // setInterval(checkNotifications, 30000);
    </script>
    <?php endif; ?>
</body>
</html>