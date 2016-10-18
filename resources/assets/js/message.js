var Message = function()
{
    var self = this,
        params = {
            monitored: false
        };

    var closeForm = function()
    {
        $('#messageBody').val('');
        $('button[data-target="#messageNewPanel"]').click();
    };

    self.post = function()
    {
        var $form = $('#messageNewPanel').find('form'),
            $btn = $form.find('[type=submit]').attr('disabled', true),
            msgBody = $('#messageBody').val();

        $.ajax({
            url: $form.data('action'),
            method: $form.attr('method') || 'POST',
            data: $form.serialize(),
            success: function(data) {
                if (data && data.success) {
                    if (!params.monitored) {
                        self.updateList();
                    }
                    else {
                        self.notifyMessageServer(msgBody);
                    }
                    closeForm();
                }
            },
            error: function(xhr) {
                alert('Error '+xhr.status+': '+xhr.statusText);
            },
            complete: function() {
                $btn.attr('disabled', false);
            }
        });
    };

    self.updateList = function()
    {
        // @todo update list without ajax request
        $.ajax({
            dataType: 'html',
            success: function(data) {
                $('#messages').replaceWith( $(data).find('#messages') );
            }
        });
    };

    self.setMonitored = function(enabled)
    {
        params.monitored = !!enabled;
    };

    self.notifyMessageServer = function(message) {};

    self.onNewMessage = function(message)
    {
        self.updateList();
    };
};

module.exports = Message;