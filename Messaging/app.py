from flask import Flask, render_template, request, redirect, url_for
import mysql.connector
import string
import random

app = Flask(__name__)

# Database Configuration
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="test",
    database="messages1"
)
cursor = db.cursor()

# Create Messages Table
cursor.execute("""
    CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        link VARCHAR(255) NOT NULL
    )
""")
db.commit()

def generate_random_link():
    characters = string.ascii_letters + string.digits
    return ''.join(random.choice(characters) for i in range(10))

def add_redirect_link(message):
    link = generate_random_link()
    return f"{message} <a href='/redirect_page/{link}'>Click here</a> to redirect."

@app.route('/')
def index():
    # Fetch messages from the database
    cursor.execute("SELECT * FROM messages")
    messages = cursor.fetchall()
    return render_template('index.html', messages=messages)

@app.route('/add_message', methods=['POST'])
def add_message():
    sender = request.form['sender']
    original_message = request.form['message']
    message_with_link = add_redirect_link(original_message)

    # Insert message into the database
    cursor.execute("INSERT INTO messages (sender, message, link) VALUES (%s, %s, %s)", (sender, original_message, message_with_link))
    db.commit()

    return redirect(url_for('index'))

@app.route('/redirect_page/<link>')
def redirect_page(link):
    # Fetch the message with the given link
    cursor.execute("SELECT * FROM messages WHERE link LIKE %s", (f"%{link}%",))
    message = cursor.fetchone()

    if message:
        return render_template('redirect_page.html', message=message)
    else:
        return "Message not found."

if __name__ == '__main__':
    app.run(debug=True)
