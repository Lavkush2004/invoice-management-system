<?php
require_once __DIR__ . '/includes/auth_check.php';
require_login(array('admin', 'vendor'));

if (isset($_REQUEST['number'])) {
    $number = intval($_REQUEST['number']);
    ?>
    <div class="row add-div-row" id="add_<?php echo $number; ?>">
        <br/>
        <div class="col-lg-2">
            <input type="text" name="product[]" class="form-control" id="product_<?php echo $number; ?>" value="">
        </div>
        <div class="col-lg-2">
            <input type="number" name="price[]" class="form-control" min="0" id="price_<?php echo $number; ?>" value="" onchange="getTotal(<?php echo $number; ?>)">
        </div>
        <div class="col-lg-2">
            <input type="number" name="quantity[]" class="form-control" min="1" id="quantity_<?php echo $number; ?>" value="" onchange="getTotal(<?php echo $number; ?>)">
        </div>
        <div class="col-lg-2">
            <input type="number" name="gst[]" class="form-control" min="0" id="gst_<?php echo $number; ?>" value="" onchange="getTotal(<?php echo $number; ?>)">
        </div>
        <div class="col-lg-2">
            <input type="number" name="totalAmt[]" class="form-control totalPrice" readonly="readonly" min="0" id="total_<?php echo $number; ?>" value="">
        </div>
        <div class="col-lg-2">
            <a href="javascript:;" onclick="add_div(<?php echo $number; ?>)">
                <i class="fa fa-plus-circle" aria-hidden="true"></i>
            </a>
            <a href="javascript:;" onclick="remove_div(<?php echo $number; ?>)">
                <i class="fa fa-minus-circle" aria-hidden="true"></i>
            </a>
        </div>
    </div>
<?php } ?>