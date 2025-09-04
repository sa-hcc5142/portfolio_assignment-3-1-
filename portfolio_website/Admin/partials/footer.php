 </main>
 <footer class="admin-footer container">
     <?php if (!empty($_COOKIE['last_login_at'])): ?>
         <small class="muted">Last admin sign-in: <?= htmlspecialchars($_COOKIE['last_login_at']) ?></small>
     <?php endif; ?>
 </footer>
 </body>

 </html>