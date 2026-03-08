function updateSmsStats() {
    $('#smsMessage').text('');

    $.getJSON('/api/data/get_sms_api_token.php', function(summaryResponse) {
        if (summaryResponse.success) {
            var data = summaryResponse.data;
            $('#smsPurchased').text(data.total_purchased);
            $('#smsUsed').text(data.used);
            $('#smsBalance').text(data.balance);
            $('#smsMessage').text('');
        } else {
            $('#smsMessage').text('Failed to load SMS summary: ' + (summaryResponse.message || 'Unknown error'));
            $('#smsPurchased, #smsUsed, #smsBalance').text('-');
        }
    }).fail(function() {
        $('#smsMessage').text('Error fetching SMS summary data.');
        $('#smsPurchased, #smsUsed, #smsBalance').text('-');
    });
}

$(document).ready(function () {
    updateSmsStats();
    setInterval(updateSmsStats, 30000);
});
