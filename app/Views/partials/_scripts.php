    <script>
        const BASE_URL = "<?= base_url() ?>";
    </script>
    <!-- Core: Global state, helpers, navigation -->
    <script src="<?= base_url('js/app.js?v=' . time()) ?>"></script>
    <!-- Domain Modules -->
    <script src="<?= base_url('js/modules/app-client.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-payroll-scheme.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-tax.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-compensation.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-workspace.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-pkwt.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-process.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-umr.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-employee.js?v=' . time()) ?>"></script>
    <!-- Existing separate modules -->
    <script src="<?= base_url('js/app-org.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/app-location.js?v=' . time()) ?>"></script>
