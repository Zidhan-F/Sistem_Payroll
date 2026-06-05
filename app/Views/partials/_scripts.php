    <script>
        const BASE_URL = "<?= base_url() ?>";
        const API_URL = BASE_URL + 'index.php/api';
        window.API = API_URL;
    </script>
    <!-- TomSelect for searchable dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    
    <!-- SheetJS for client-side Excel (.xlsx) export -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    
    <!-- html2pdf.js for client-side PDF export -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <!-- Core: Global state, helpers, navigation -->
    <script src="<?= base_url('js/app.js?v=' . time()) ?>"></script>
    <!-- Domain Modules -->
    <script src="<?= base_url('js/modules/app-client.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-payroll-scheme.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-payroll-scheme-templates.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-tax.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-compensation.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-workspace.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-pkwt.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-process.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-umr.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-employee.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-schedule.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-holiday.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-attendance.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-overtime.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-settings.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/modules/app-shift.js?v=' . time()) ?>"></script>
    <!-- Existing separate modules -->
    <script src="<?= base_url('js/app-org.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/app-global-sto.js?v=' . time()) ?>"></script>
    <script src="<?= base_url('js/app-location.js?v=' . time()) ?>"></script>
