<?php
defined('_JEXEC') || die('=;)');
?>
<table class="table">
    <tbody>
    <tr>
        <th>شناسه</th>
        <th>شماره سفارش</th>
        <th>شماره پیگیری پرداخت</th>
        <th>مبلغ (تومان)</th>
        <th>تاریخ</th>
    </tr>
    <?php
    $db = JFactory::getDBO();
    $query = "SELECT * FROM #__jshopping_zarinpal";
    $db->setQuery($query);
    $payments = $db->loadObjectList();
    foreach ($payments as $payment) {
        ?>
        <tr>
            <td><?php echo $payment->id ?></td>
            <td>
                <a href="index.php?option=com_jshopping&controller=orders&task=show&order_id=<?php echo $payment->order_id ?>"><?php echo $payment->order_id ?></a>
            </td>
            <td><?php echo $payment->ref_id ?></td>
            <td><?php echo $payment->amount ?></td>
            <td><?php echo JHTML::_('date', $payment->created_at, "Y/m/d - G:i:s") ?></td>
        </tr>
    <?php } //tar?>
    </tbody>
</table>
