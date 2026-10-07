jQuery(function($) {
    let offset = 0;
    let file_offset = 0;
    let file_index = 0;
    let aborted = false;

    const PAUSE_EVERY = 1000;
    let nextCheckpoint = offset + PAUSE_EVERY;

    let total = 0;
    let totalImported = 0;
    let totalSkipped = 0;
    let totalFailed = 0;

    // Handle the "Continue" button click event
    function handleContinueClick() {
        $('#cj-import-continue').remove(); 
        $('#cj-import-start').prop('disabled', true).show(); 
        $('#cj-import-log').append('<br><strong>Resuming...</strong><br>');
        runBatch();
    }

    $.post(CjBatchImport.ajax_url, {
        action: 'cj_batch_import',
        security: CjBatchImport.nonce,
        check_only: true
    }, function(res) {
        if (res.success) {
            offset = res.data.offset ?? 0;
            file_offset = res.data.file_offset || 0;
            file_index = res.data.file_index || 0;
            total = res.data.total || 0;

            if (offset >= total && total > 0) {
                offset = total;
            }

            if (offset > 0) {
                const displayOffset = offset + 0;
                $('#cj-import-start').removeClass('button-primary').addClass('button-secondary')
                    .text(`Continue Import (${displayOffset}/${total})`);
                $('#cj-status-text').html('<span style="color:purple;">Resuming previous import…</span>');
            }

            nextCheckpoint = offset + PAUSE_EVERY;
            nextCheckpoint = Math.min(nextCheckpoint, total); 
        }
    });

    function updateUI(data) {
        // Update progress bar
        const progress = data.progress || 0;
        $('#cj-progress-bar').val(progress);

        // Update the progress text with the corrected offset (adding 0 to the current offset)
        const correctedOffset = data.offset + 0 || 0;  // Add 0 to the current offset
        $('#cj-progress-text').text(`${correctedOffset}/${data.total || 0} (${progress}%)`);

        // Update counters
        if (data.imported !== undefined) totalImported += data.imported;
        if (data.skipped !== undefined) totalSkipped += data.skipped;
        if (data.failed !== undefined) totalFailed += data.failed;

        $('#cj-imported-count').text(totalImported);
        $('#cj-skipped-count').text(totalSkipped);
        $('#cj-failed-count').text(totalFailed);

        // Update status text
        if (data.done || data.aborted) {
            const statusText = data.aborted 
                ? '<span style="color:red;">Aborted</span>' 
                : '<span style="color:green;">Completed</span>';
            $('#cj-status-text').html(statusText);
        } else {
            const fileName = data.current_file || 'Processing...';
            $('#cj-status-text').html(`<span style="color:blue;">🔄 Processing: ${fileName}</span>`);
        }

        // Logs and Auto-Scrolling
        const logs = Array.isArray(data.log) ? data.log : [];
        if (logs.length) {
            const timestamp = new Date().toLocaleTimeString();
            const logHtml = logs.map(log => `[${timestamp}] ${log}`).join('<br>');
            $('#cj-import-log').append(logHtml + '<br>');

            const logDiv = document.getElementById('cj-import-log');
            if (logDiv) logDiv.scrollTop = logDiv.scrollHeight;
        }
    }

    function runBatch() {
        if (aborted) return;
        
        $('#cj-import-start').prop('disabled', true).text('Importing...');
        $('#cj-import-start-new').prop('disabled', true);
        $('#cj-import-abort').prop('disabled', false);
        $('#cj-import-continue').remove(); 

        $.ajax({
            url: CjBatchImport.ajax_url,
            type: 'POST',
            data: {
                action: 'cj_batch_import',
                security: CjBatchImport.nonce,
                offset: offset,
                file_offset: file_offset,
                file_index: file_index,
                aborted: aborted
            },
            success: function(res) {
                if (res.success) {
                    let data = res.data;
                    offset = data.offset || offset;
                    file_offset = data.file_offset || 0;
                    file_index = data.file_index || file_index;
                    if (total === 0 && data.total) {
                        total = data.total;
                    }
                    
                    updateUI(data);

                    if (data.done || data.aborted) {
                        $('#cj-import-start').prop('disabled', false).text('Start New Import').removeClass('button-secondary').addClass('button-primary').show();
                        $('#cj-import-start-new').prop('disabled', false);
                        $('#cj-import-abort').prop('disabled', true);
                        return;
                    }

                    if (offset > total) offset = total;

                    if (offset >= nextCheckpoint) {
                        nextCheckpoint = Math.min(nextCheckpoint + PAUSE_EVERY, total);
                        $('#cj-status-text').html(`<span style="color:orange; font-weight:bold;">⏸ Paused at ${offset}/${total}</span>`);

                        if ($('#cj-import-continue').length === 0) {
                            const $continueBtn = $('<button id="cj-import-continue" class="button button-primary">Continue Import</button>');
                            $continueBtn.on('click', handleContinueClick);
                            $('#cj-import-start').after($continueBtn);
                        }

                        $('#cj-import-start').hide();
                        $('#cj-import-start-new').prop('disabled', false);
                        return;
                    }

                    setTimeout(runBatch, 500);
                } else {
                    const errorMsg = res.data && res.data.message ? res.data.message : 'Unknown error';
                    $('#cj-import-log').append(`<br><span style="color:red;"><strong>Error: ${errorMsg}</strong></span><br>`);
                    $('#cj-import-start').prop('disabled', false).text('Retry').show();
                    $('#cj-import-start-new').prop('disabled', false);
                }
            },
            error: function(xhr, status, error) {
                $('#cj-import-log').append(`<br><span style="color:red;"><strong>AJAX Error: ${error}</strong></span><br>`);
                setTimeout(runBatch, 3000);
            }
        });
    }

    $('#cj-import-start').on('click', function() {
        const btnText = $(this).text();
        const isContinuing = btnText.includes('Continue') || btnText.includes('Retry');
        $('#cj-import-continue').remove();
        $(this).show();

        if (!isContinuing) {
            if (offset > 0 && !confirm("Start from the beginning?")) return;
            
            offset = 0; 
            file_offset = 0;
            file_index = 0;
            aborted = false;
            nextCheckpoint = offset + PAUSE_EVERY; 
            nextCheckpoint = Math.min(nextCheckpoint, total);
            
            totalImported = 0;
            totalSkipped = 0;
            totalFailed = 0;
            
            $('#cj-import-log').html('<strong>Starting import...</strong><br>');
            $(this).addClass('button-primary').removeClass('button-secondary');
        } else {
            $('#cj-import-log').append('<br><strong>Resuming...</strong><br>');
            $(this).addClass('button-primary').removeClass('button-secondary');
        }

        runBatch();
    });

    $('#cj-import-start-new').on('click', function() {
        if (!confirm('Start a completely new import from the beginning? This will reset all progress')) {
            return;
        }

        $('#cj-status-text').html('<span style="color:blue;">Resetting progress...</span>');
        $('#cj-import-log').html('<strong>Starting NEW import from beginning...</strong><br>');

        $.post(CjBatchImport.ajax_url, {
            action: 'cj_batch_import',
            security: CjBatchImport.nonce,
            reset_total: true
        }, function() {
            offset = 0; 
            file_offset = 0;
            file_index = 0;
            aborted = false;
            nextCheckpoint = PAUSE_EVERY;

            total = 0;
            totalImported = 0;
            totalSkipped = 0;
            totalFailed = 0;

            // UI reset
            $('#cj-progress-bar').val(0);
            $('#cj-progress-text').text('0/0 (0%)');
            $('#cj-imported-count').text('0');
            $('#cj-skipped-count').text('0');
            $('#cj-failed-count').text('0');

            $('#cj-import-continue').remove();
            $('#cj-import-start').text('Start Import')
                .removeClass('button-secondary')
                .addClass('button-primary')
                .show();

            runBatch();
        });
    });

    $('#cj-import-abort').on('click', function() {
        if (confirm('Stop import completely?')) {
            aborted = true;
            $(this).prop('disabled', true);
            $('#cj-import-start').prop('disabled', true);
            $('#cj-import-continue').remove();
            $('#cj-status-text').html('<span style="color:red;">Aborting...</span>');
        }
    });
});
