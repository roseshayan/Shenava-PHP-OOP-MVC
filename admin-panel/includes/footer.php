<?php
/**
 * Shenava - Admin Footer
 * Includes all JS dependencies
 */
?>
<!-- Scripts -->
<script src="../../../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../../node_modules/jquery/dist/jquery.min.js"></script>
<script src="../../../node_modules/datatables.net/js/dataTables.min.js"></script>
<script src="../../../node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<script src="../../../node_modules/@simonwep/pickr/dist/pickr.min.js"></script>

<!-- Custom JS -->
<script src="../../js/app.js"></script>

<?php if (isset($pageScript)): ?>
    <script src="../js/<?php echo $pageScript; ?>"></script>
<?php endif; ?>
</body>
</html>