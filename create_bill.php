<?php
require_once __DIR__ . '/includes/auth_check.php';
$userRole = require_login(array('admin', 'vendor'));
require_once __DIR__ . '/includes/header.php';
?>
<body>
    <!-- wrapper -->
    <div id="wrapper">
        <!-- navbar top -->
        <?php require_once __DIR__ . '/includes/upper_menu.php'; ?>
        <!-- end navbar top -->

        <!-- navbar side -->
        <?php require_once __DIR__ . '/includes/left_menu.php';?>

        <?php
        // Get company ID from session
        $comp_id = ($userRole === 'admin') ? get_current_company_id() : (int)$_SESSION['vendor_data']['userid'];

        if ($userRole === 'vendor') {
            $comp_id = (int)$_SESSION['vendor_data']['userid'];
        }

        $customer_list = array();

        // Get customers for this company
        $table = 'customers';
        $fields = '*';
        $cond = ' company_id = '.$comp_id;
        $customer_data = Select_Some($fields,$table,$cond);
        if(is_object($customer_data) && mysqli_num_rows($customer_data)>0)
        {
            while($d = mysqli_fetch_assoc($customer_data))
            {
                array_push($customer_list,$d);
            }
        }
        ?>

        <!-- end navbar side -->
        <!-- page-wrapper -->
        <div id="page-wrapper">
            <div class="row">
                <!-- page header -->
                <div class="col-lg-12">
                    <h1 class="page-header">Create Bill</h1>
                </div>
                <!-- end page header -->
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <!-- Advanced Tables -->
                    <div class="panel panel-default">
                        <form id="" name="" method="post" action="<?php echo APP_URL.'model/common.php'?>">
                            <div class="panel-heading">
                                <div class="row">
                                    <div class="col-lg-2">
                                        <label>Customer List</label>
                                    </div>
                                    <div class="col-lg-7">
                                        <input type="hidden" name="comp_id" id="comp_id" value="<?php echo (int)get_current_company_id(); ?>">
                                        <input type="hidden" name="action" id="action" value="create_invoice">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(require_csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">
                                        <select class="form-control" name="cust_id" id="cust_id">
                                            <option value="">--Select Customer--</option>
                                            <option value="new_customer">+ Add Customer</option>
                                            <?php if(count($customer_list)){
                                            foreach ($customer_list as $ckey => $cvalue) {
                                            ?>
                                            <option value="<?php echo $cvalue['cus_id']; ?>"><?php echo $cvalue['cus_name']; ?>/<?php echo $cvalue['cus_mobile']; ?></option>
                                            <?php } }?>
                                        </select>

                                        <div id="customerAddBox" style="display:none; margin-top:15px; border:1px solid #ddd; border-radius:4px; padding:15px; background:#fafafa;">
                                            <div class="row">
                                                <div class="col-lg-4">
                                                    <label>Name</label>
                                                    <input type="text" id="new_customer_name" class="form-control" placeholder="Customer name">
                                                </div>
                                                <div class="col-lg-3">
                                                    <label>Phone</label>
                                                    <input type="text" id="new_customer_phone" class="form-control" placeholder="Phone number">
                                                </div>
                                                <div class="col-lg-4">
                                                    <label>Address</label>
                                                    <input type="text" id="new_customer_address" class="form-control" placeholder="Address">
                                                </div>
                                                <div class="col-lg-1">
                                                    <label>&nbsp;</label>
                                                    <button type="button" id="saveCustomerBtn" class="btn btn-primary btn-block">Save</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3">
                                        <button type="button" class="btn btn-success" onclick="submitBill()" style="width:100%; height:40px;">Create Bill</button>
                                    </div>
                                </div>
                            </div>

                            <div class="panel-body">
                                <div class="row" id="add_0">
                                    <div class="col-lg-2">
                                        <label class="">Product Name</label>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="">Price</label>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="">Quantity</label>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="">GST</label>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="">Total</label>
                                    </div>
                                    <div class="col-lg-2">
                                        <label class="">Action</label>
                                    </div>
                                </div>

                                <div class="row" id="">
                                    <br/>
                                    <div class="col-lg-8">
                                        <label class="">Total</label>
                                    </div>
                                    <div class="col-lg-2">
                                        <input class="form-control" type="text" readonly="true" name="totalPrice" id="totalPrice" value="">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!--End Advanced Tables -->
                </div>
            </div>
        </div>
        <!-- end page-wrapper -->
    </div>
    <!-- end wrapper -->

    <!-- Core Scripts - Include with every page -->
    <?php require_once __DIR__ . '/includes/footer.php';?>

    <script>
        var APP_URL = "<?php echo APP_URL; ?>";
        var div_id = 0;

        document.addEventListener('DOMContentLoaded', function () {
            add_div(0);

            var customerSelect = document.getElementById('cust_id');
            var customerAddBox = document.getElementById('customerAddBox');
            var saveCustomerBtn = document.getElementById('saveCustomerBtn');

            if (customerSelect) {
                customerSelect.addEventListener('change', function () {
                    if (this.value === 'new_customer') {
                        customerAddBox.style.display = 'block';
                    } else {
                        customerAddBox.style.display = 'none';
                    }
                });
            }

            if (saveCustomerBtn) {
                saveCustomerBtn.addEventListener('click', function () {
                    var name = document.getElementById('new_customer_name').value.trim();
                    var phone = document.getElementById('new_customer_phone').value.trim();
                    var address = document.getElementById('new_customer_address').value.trim();

                    if (!name || !phone || !address) {
                        alert('Please enter customer name, phone number and address.');
                        return;
                    }

                    var formData = new URLSearchParams();
                    formData.append('action', 'save_customer_inline');
                    formData.append('comp_id', document.getElementById('comp_id').value);
                    formData.append('name', name);
                    formData.append('mobile', phone);
                    formData.append('address', address);
                    var csrfInput = document.querySelector('input[name="csrf_token"]');
                    if (csrfInput) {
                        formData.append('csrf_token', csrfInput.value);
                    }

                    fetch(APP_URL + 'model/common.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                        },
                        body: formData.toString()
                    })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (result) {
                        if (result && result.success && result.customer) {
                            var option = document.createElement('option');
                            option.value = result.customer.id;
                            option.text = result.customer.name + '/' + result.customer.mobile;
                            customerSelect.add(option);
                            customerSelect.value = result.customer.id;
                            customerAddBox.style.display = 'none';
                            document.getElementById('new_customer_name').value = '';
                            document.getElementById('new_customer_phone').value = '';
                            document.getElementById('new_customer_address').value = '';
                        } else {
                            alert(result && result.message ? result.message : 'Customer could not be saved.');
                        }
                    })
                    .catch(function () {
                        alert('Customer save failed. Please try again.');
                    });
                });
            }
        });

        function remove_div(number)
        {
            var row = document.getElementById('add_' + number);
            var total_div = document.querySelectorAll('.add-div-row');
            if (row && number !== '' && total_div.length > 1) {
                row.parentNode.removeChild(row);
                getFinalTotal();
            }
        }

        function add_div(number)
        {
            var old_number = number;
            div_id++;
            var formData = new URLSearchParams();
            formData.append('number', div_id);

            fetch(APP_URL + 'div_page.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: formData.toString()
            })
            .then(function (response) {
                if (!response.ok) return '';
                return response.text();
            })
            .then(function (suc) {
                if (suc && suc.trim() !== '' && !suc.includes('<!DOCTYPE') && !suc.includes('<html')) {
                    var anchor = document.getElementById('add_' + old_number);
                    if (anchor) {
                        anchor.insertAdjacentHTML('afterend', suc);
                    }
                }
            })
            .catch(function (error) {
                console.error('add_div failed:', error);
            });
        }

        function getTotal(number)
        {
            var priceField = document.getElementById('price_' + number);
            var quantityField = document.getElementById('quantity_' + number);
            var gstField = document.getElementById('gst_' + number);
            var totalField = document.getElementById('total_' + number);
            var totalPriceField = document.getElementById('totalPrice');

            if (!priceField || !quantityField || !totalField) {
                return;
            }

            var price = parseFloat(priceField.value);
            var quantity = parseFloat(quantityField.value);
            var gst = parseFloat(gstField ? gstField.value : 0);
            var total = '';

            if ((price !== '' && price > 0 && !isNaN(price)) && (quantity !== '' && quantity > 0 && !isNaN(quantity))) {
                if (gst !== '' && gst > 0 && !isNaN(gst)) {
                    total = (price * quantity) + (((price * quantity) * gst) / 100);
                } else {
                    total = (price * quantity);
                }

                totalField.value = total;
                getFinalTotal();
            }
        }

        function getFinalTotal()
        {
            var sum = 0;
            var rows = document.querySelectorAll('.totalPrice');
            rows.forEach(function (field) {
                var val = parseFloat(field.value);
                if (!isNaN(val)) {
                    sum += val;
                }
            });

            sum = sum.toFixed(2);
            if (sum !== 'NaN' && sum !== undefined && sum !== '') {
                var totalPriceField = document.getElementById('totalPrice');
                if (totalPriceField) {
                    totalPriceField.value = sum;
                }
            }
        }

        function submitBill() {
            var custId = document.getElementById('cust_id').value;
            var totalPrice = document.getElementById('totalPrice').value;

            console.log('submitBill called: custId=' + custId + ', totalPrice=' + totalPrice);

            if (!custId || custId === '') {
                alert('Please select a customer.');
                return false;
            }

            if (!totalPrice || totalPrice === '0' || totalPrice === '0.00') {
                alert('Please add at least one product with valid price and quantity.');
                return false;
            }

            var form = document.querySelector('form');
            if (form) {
                console.log('Submitting form to: ' + form.action);
                form.submit();
            } else {
                alert('Form not found');
            }
        }
    </script>
</body>
</html>