<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/js/bootstrap.min.js"></script>
<script src="<?php echo APP_URL;?>assets/plugins/metismenu.js"></script>
<script src="<?php echo APP_URL;?>assets/scripts/siminta.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var body = document.body;
        var toggle = document.getElementById('sidebarToggle');

        function updateToggleState() {
            var isCollapsed = body.classList.contains('sidebar-collapsed');
            if (toggle) {
                toggle.setAttribute('aria-expanded', String(!isCollapsed));
                toggle.classList.toggle('is-active', !isCollapsed);
            }
        }

        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                body.classList.toggle('sidebar-collapsed');
                updateToggleState();
            });
        }

        window.addEventListener('resize', function () {
            if (window.innerWidth > 768) {
                body.classList.remove('sidebar-collapsed');
            }
            updateToggleState();
        });

        document.querySelectorAll('[data-toggle="dropdown"]').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var dropdownRoot = button.closest('.dropdown');
                var menu = dropdownRoot ? dropdownRoot.querySelector('.dropdown-menu') : button.parentElement.querySelector('.dropdown-menu');
                var willOpen = !(menu && menu.classList.contains('open'));

                document.querySelectorAll('.dropdown-menu.open').forEach(function (openMenu) {
                    openMenu.classList.remove('open');
                });
                document.querySelectorAll('.dropdown.open').forEach(function (openDropdown) {
                    openDropdown.classList.remove('open');
                });

                if (menu && willOpen) {
                    menu.classList.add('open');
                    if (dropdownRoot) {
                        dropdownRoot.classList.add('open');
                    }
                    button.setAttribute('aria-expanded', 'true');
                } else {
                    button.setAttribute('aria-expanded', 'false');
                }
            });
        });

        document.addEventListener('click', function (event) {
            if (!event.target.closest('.dropdown')) {
                document.querySelectorAll('.dropdown-menu.open').forEach(function (menu) {
                    menu.classList.remove('open');
                });
                document.querySelectorAll('.dropdown.open').forEach(function (dropdown) {
                    dropdown.classList.remove('open');
                });
            }
        });

        updateToggleState();
    });
</script>

<!-- Page-Level Plugin Scripts-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>

<?php include_once __DIR__ . '/chatbot_widget.php'; ?>

<?php
include_once(APP_ROOT_URL.'includes/main.php');
?>
