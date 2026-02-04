   
    </div>
    <footer class="bg-dark text-white text-center py-3 mt-4">
        <div class="container">
            <p> <?php echo date('Y'); ?> Internship Portal.</p>
            <p class="mb-0">
                <a href="<?= BASE_URL ?>index.php" class="text-white me-3">Home</a>
                <?php if(!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])): ?>
                    <a href="<?= BASE_URL ?>auth/login.php" class="text-white me-3">User Login</a>
                    <a href="<?= BASE_URL ?>auth/admin_login.php" class="text-warning">Admin Login</a>
                <?php endif; ?>
            </p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>