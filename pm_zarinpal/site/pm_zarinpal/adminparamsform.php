<div class="col100">
<fieldset class="adminform">


<table style="text-align:right" class="admintable" width = "100%" >
 <tr>
   <td class="key">
     <span>کد پذیرنده</span>
   </td>
   <td style="text-align: right;">
     <input type = "text" class = "inputbox" name = "pm_params[merchant_id]" size="50" value = "<?php echo $params['merchant_id']?>" />
   </td>
 </tr>
 <tr>
   <td class="key">
     <span>حالت تستی</span>
   </td>
   <td style="text-align: right;">
       <?php print JHTML::_('select.booleanlist', 'pm_params[testmode]', [], $params['testmode'], JText::_('JYES'), JText::_('JNO'));?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <span>نوع درگاه</span>
   </td>
   <td style="text-align: right;">
       <?php print JHTML::_('select.booleanlist', 'pm_params[gate_type]', [], $params['gate_type'], 'زرین گیت', 'وب گیت');?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <span>واحد پول</span>
   </td>
   <td style="text-align: right;">
       <?php print JHTML::_('select.booleanlist', 'pm_params[currency]', [], $params['currency'], 'ریال', 'تومان');?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_END;?>
   </td>
   <td style="text-align: right;">
     <?php              
     print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_PENDING;?>
   </td>
   <td style="text-align: right;">
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status']);
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo _JSHOP_TRANSACTION_FAILED;?>
   </td>
   <td style="text-align: right;">
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_failed_status']);
     ?>
   </td>
 </tr>
</table>
</fieldset>
</div>
<div class="clr"></div>