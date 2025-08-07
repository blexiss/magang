<!DOCTYPE html>
<html>
<head>
    <title>Inventory App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <style>

    .toast-notify {
    font-family: var(--bs-body-font-family, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif);
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #198754; /* Bootstrap success green */
    color: #fff;
    border-radius: 8px;
    width: 280px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 1050;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: fadeIn 0.3s ease forwards;
}

.toast-notify .toast-content {
    padding: 15px 15px 5px 15px;
    position: relative;
}

.toast-notify .toast-message {
    font-size: 1rem;
    margin-bottom: 0;
    display: block;
}

.toast-notify .toast-close {
    position: absolute;
    top: 8px;
    right: 8px;
    background: transparent;
    border: none;
    color: #ddd;
    font-size: 1.2rem;
    cursor: pointer;
    line-height: 1;
    transition: color 0.2s ease;
}

.toast-notify .toast-close:hover {
    color: #fff;
}

.toast-notify .toast-progress {
    height: 3px;
    background-color: #0f5132; /* darker green */
    width: 100%;
    position: absolute;
    bottom: 0;
    left: 0;
}



    .toast-confirm {
        font-family: var(--bs-body-font-family, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif);
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #333;
        color: #fff;
        border-radius: 8px;
        width: 280px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 1050;
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: fadeIn 0.3s ease forwards;
    }

    .toast-content {
        padding: 15px 15px 5px 15px;
        position: relative;
    }

    .toast-message {
        font-size: 1rem;
        margin-bottom: 10px;
        display: block;
    }

    .toast-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }

    .btn-confirm, .btn-cancel {
        font-family: inherit;
        cursor: pointer;
        padding: 5px 10px;
        margin-bottom: 2px;
        border-radius: 4px;
        border: none;
        font-size: 0.9rem;
        font-weight: 400;
        transition: background-color 0.2s ease;
    }

    .btn-confirm {
        background-color: #dc3545;
        color: white;
    }

    .btn-confirm:hover {
        background-color: #b02a37;
    }

    .btn-cancel {
        background-color: #6c757d;
        color: white;
    }

    .btn-cancel:hover {
        background-color: #565e64;
    }

    .toast-close {
        position: absolute;
        top: 8px;
        right: 8px;
        background: transparent;
        border: none;
        color: #aaa;
        font-size: 1.2rem;
        cursor: pointer;
        line-height: 1;
        transition: color 0.2s ease;
    }

    .toast-close:hover {
        color: white;
    }

    .toast-progress {
        height: 3px;
        background-color: red;
        width: 100%;
        position: absolute;
        bottom: 0;
        left: 0;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px);}
        to { opacity: 1; transform: translateY(0);}
    }

    @keyframes countdown {
        from { width: 100%; }
        to { width: 0%; }
    }
    </style>
</head>

<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container d-flex align-items-center">
        <a class="navbar-brand" href="{{ route('inventory.index') }}">Inventory App</a>

        <form class="d-flex ms-auto me-3 align-items-center" role="search" method="GET" action="{{ route('inventory.index') }}">
            <input class="form-control form-control-sm" 
                   type="search" 
                   placeholder="Search ID or Name" 
                   aria-label="Search"
                   name="search"
                   value="{{ request('search') }}"
                   style="height: 32px;"> {{-- Adjust if needed --}}
        </form>

        <div>
            <a href="{{ route('audit.logs') }}" class="btn btn-outline-light btn-sm">Audit Log</a>
        </div>
    </div>
</nav>




<div id="confirm-toast" class="toast-confirm" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="toast-content">
        <span class="toast-message">Delete this item?</span>
        <div class="toast-actions">
            <button id="confirm-btn" class="btn-confirm">Confirm</button>
            <button id="cancel-btn" class="btn-cancel">Cancel</button>
        </div>
        <button id="close-toast" class="toast-close" aria-label="Close">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>

<div id="notify-toast" class="toast-notify" role="alert" aria-live="polite" aria-atomic="true" style="display:none;">
    <div class="toast-content">
        <span class="toast-message"></span>
        <button id="notify-close" class="toast-close" aria-label="Close">&times;</button>
        <div class="toast-progress"></div>
    </div>
</div>


<div class="container">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>



<script>
    async function flushInventoryQueue() {
        const queue = JSON.parse(localStorage.getItem('pending_inventory') || '[]');
        if (!queue.length) return;

        const newQueue = [];

        for (let item of queue) {
            try {
                let url = '/inventory';
                let method = 'POST';

                if (item.action === 'edit' && item.id) {
                    url += '/' + item.id;
                    method = 'PUT';
                }

                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(item.data)
                });

                if (!res.ok) throw new Error("Failed to sync");

            } catch (err) {
                newQueue.push(item);
            }
        }

        localStorage.setItem('pending_inventory', JSON.stringify(newQueue));

        if (newQueue.length === 0 && queue.length > 0) {
            console.log("✅ All pending items synced.");
            alert("✅ Offline changes have been synced.");
        }
    }

    setInterval(flushInventoryQueue, 5000);

    function showConfirmToast(message = 'Are you sure?', timeout = 4000) {
        return new Promise(resolve => {
            const toast = document.getElementById('confirm-toast');
            const confirmBtn = document.getElementById('confirm-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const closeBtn = document.getElementById('close-toast');
            const progressBar = toast.querySelector('.toast-progress');
            const messageEl = toast.querySelector('.toast-message');

            messageEl.textContent = message;
            toast.style.display = 'flex';

            // Reset progress bar animation
            progressBar.style.animation = 'none';
            void progressBar.offsetWidth;
            progressBar.style.animation = `countdown ${timeout / 1000}s linear forwards`;

            let resolved = false;

            const cleanup = () => {
                if (!resolved) resolve(false);
                toast.style.display = 'none';
                resolved = true;
                confirmBtn.onclick = null;
                cancelBtn.onclick = null;
                closeBtn.onclick = null;
            };

            confirmBtn.onclick = () => {
                if (!resolved) {
                    resolve(true);
                    toast.style.display = 'none';
                    resolved = true;
                    confirmBtn.onclick = null;
                    cancelBtn.onclick = null;
                    closeBtn.onclick = null;
                }
            };

            cancelBtn.onclick = cleanup;
            closeBtn.onclick = cleanup;

            setTimeout(cleanup, timeout);
        });
    }
    
    function showNotifyToast(message = 'Success!', timeout = 3000) {
    return new Promise(resolve => {
        const toast = document.getElementById('notify-toast');
        const messageEl = toast.querySelector('.toast-message');
        const closeBtn = document.getElementById('notify-close');
        const progressBar = toast.querySelector('.toast-progress');

        messageEl.textContent = message;
        toast.style.display = 'flex';

        // Reset progress bar animation
        progressBar.style.animation = 'none';
        void progressBar.offsetWidth;
        progressBar.style.animation = `countdown ${timeout / 1000}s linear forwards`;

        let resolved = false;

        const cleanup = () => {
            if (!resolved) resolve();
            toast.style.display = 'none';
            resolved = true;
            closeBtn.onclick = null;
            clearTimeout(timer);
        };

        closeBtn.onclick = cleanup;

        // Auto close after timeout
        const timer = setTimeout(cleanup, timeout);
    });
}

</script>

</body>
</html>
