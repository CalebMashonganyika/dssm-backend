document.getElementById('paymentForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const messageDiv = document.getElementById('message');
    messageDiv.textContent = '';
    messageDiv.className = 'message';

    const phone = document.getElementById('phone').value.trim();
    const email = document.getElementById('email').value.trim();
    const amount = parseFloat(document.getElementById('amount').value);

    if (!phone || !email || !amount || amount <= 0) {
        showMessage('Please fill in all fields correctly.', 'error');
        return;
    }

    const reference = 'sub_' + Date.now();

    try {
        const response = await fetch('/paynow/initiate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                reference: reference,
                amount: amount,
                phone: phone,
                email: email
            })
        });

        const result = await response.json();

        if (result.success) {
            // Redirect to Paynow
            window.location.href = result.redirect_url;
        } else {
            showMessage(result.error || 'Payment initiation failed.', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('An error occurred. Please try again.', 'error');
    }
});

function showMessage(text, type) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = text;
    messageDiv.className = `message ${type}`;
}