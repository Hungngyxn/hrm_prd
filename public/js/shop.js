function openEditModal(id, shopName, shopCode, sellerId) {

    $('#editShopForm').attr('action', '/shop/' + id);
    $('#editShopId').val(id);
    $('#editShopName').val(shopName);
    $('#editShopCode').val(shopCode);
    $('#editSellerId').val(sellerId).trigger('change');
    $('#editShopModal').modal('show');
}

$(document).ready(function () {
    $('.select2').select2({
        dropdownAutoWidth: true,
        width: '100%',
        theme: 'bootstrap-5',
        closeOnSelect: true
    });

    // Tự động filter khi chọn seller
    $('.select2[name="user_id_filter"]').on('change', function () {
        $('#filterForm').submit();
    });
});

function resetFilters() {
    const form = document.getElementById('filterForm');
    form.querySelector('input[name="search"]').value = '';
    if (form.querySelector('select[name="user_id"]')) {
        $(form.querySelector('select[name="user_id"]')).val('').trigger('change');
    }
    document.getElementById('btnsearch').click();
}