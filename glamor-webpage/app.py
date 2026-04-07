from flask import Flask, render_template, request, jsonify, redirect, url_for, session
from flask_mysqldb import MySQL
import bcrypt
from datetime import datetime, timedelta

app = Flask(__name__)
app.config.from_object('config.Config')

# Initialize MySQL
mysql = MySQL(app)

# Helper function to hash passwords
def hash_password(password):
    return bcrypt.hashpw(password.encode('utf-8'), bcrypt.gensalt())

def check_password(password, hashed):
    if isinstance(hashed, str):
        hashed = hashed.encode('utf-8')
    return bcrypt.checkpw(password.encode('utf-8'), hashed)

# ============= PAGE ROUTES =============

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/sign-in')
def sign_in():
    return render_template('sign-in.html')

@app.route('/sign-up')
def sign_up():
    return render_template('sign-up.html')

@app.route('/booking')
def booking():
    return render_template('booking.html')

@app.route('/dashboard')
def dashboard():
    return render_template('dashboard.html')

# ============= CUSTOMER API ROUTES =============

@app.route('/api/register', methods=['POST'])
def register():
    try:
        data = request.get_json()
        first_name = data.get('first_name')
        last_name = data.get('last_name')
        email = data.get('email')
        phone = data.get('phone')
        password = data.get('password')

        if not all([first_name, last_name, phone, password]):
            return jsonify({'error': 'All fields are required'}), 400

        cur = mysql.connection.cursor()
        cur.execute("SELECT * FROM Customers WHERE PhoneNumber = %s OR Email = %s", (phone, email))
        existing = cur.fetchone()

        if existing:
            return jsonify({'error': 'Phone number or email already registered'}), 409

        cur.execute("""
            INSERT INTO Customers (First_Name, Last_Name, Email, PhoneNumber) 
            VALUES (%s, %s, %s, %s)
        """, (first_name, last_name, email, phone))

        customer_id = cur.lastrowid

        hashed_pw = hash_password(password)
        cur.execute("""
            INSERT INTO Users (CustomerID, Email, Password_Hash, Role) 
            VALUES (%s, %s, %s, 'customer')
        """, (customer_id, email, hashed_pw))

        mysql.connection.commit()
        cur.close()

        return jsonify({'message': 'Registration successful', 'customer_id': customer_id}), 201

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/login', methods=['POST'])
def login():
    try:
        data = request.get_json()
        email = data.get('email')
        password = data.get('password')

        cur = mysql.connection.cursor()
        cur.execute("""
            SELECT u.UserID, u.Password_Hash, u.Role, u.CustomerID, 
                   c.First_Name, c.Last_Name, c.Email, c.PhoneNumber
            FROM Users u
            LEFT JOIN Customers c ON u.CustomerID = c.CustomerID
            WHERE u.Email = %s
        """, (email,))

        user = cur.fetchone()
        cur.close()

        if not user:
            return jsonify({'error': 'Invalid email or password'}), 401

        if not check_password(password, user['Password_Hash']):
            return jsonify({'error': 'Invalid email or password'}), 401

        session['user_id'] = user['UserID']
        session['customer_id'] = user['CustomerID']
        session['role'] = user['Role']
        session['name'] = f"{user['First_Name']} {user['Last_Name']}" if user['First_Name'] else 'Admin'

        cur = mysql.connection.cursor()
        cur.execute("UPDATE Users SET Last_Login = NOW() WHERE UserID = %s", (user['UserID'],))
        mysql.connection.commit()
        cur.close()

        return jsonify({
            'message': 'Login successful',
            'user': {
                'name': session['name'],
                'role': user['Role'],
                'customer_id': user['CustomerID']
            }
        }), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/logout', methods=['POST'])
def logout():
    session.clear()
    return jsonify({'message': 'Logout successful'}), 200

# ============= SERVICE API ROUTES =============

@app.route('/api/services', methods=['GET'])
def get_services():
    try:
        cur = mysql.connection.cursor()
        cur.execute("SELECT * FROM Services ORDER BY ServiceName")
        services = cur.fetchall()
        cur.close()
        return jsonify(services), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# ============= BEAUTICIAN API ROUTES =============

@app.route('/api/beauticians', methods=['GET'])
def get_beauticians():
    try:
        cur = mysql.connection.cursor()
        cur.execute("SELECT * FROM Beauticians WHERE is_active = TRUE ORDER BY First_Name")
        beauticians = cur.fetchall()
        cur.close()
        return jsonify(beauticians), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/beauticians/availability', methods=['POST'])
def check_availability():
    try:
        data = request.get_json()
        beautician_id = data.get('beautician_id')
        appointment_date = data.get('date')
        appointment_time = data.get('time')

        cur = mysql.connection.cursor()
        cur.execute("""
            SELECT COUNT(*) as count 
            FROM Appointments 
            WHERE BeauticianID = %s 
              AND Appointment_Date = %s 
              AND Appointment_Time = %s 
              AND Status = 'scheduled'
        """, (beautician_id, appointment_date, appointment_time))

        result = cur.fetchone()
        cur.close()

        return jsonify({'available': result['count'] == 0}), 200
    except Exception as e:
        return jsonify({'error': str(e)}), 500

# ============= APPOINTMENT API ROUTES =============

@app.route('/api/appointments', methods=['POST'])
def create_appointment():
    try:
        if 'customer_id' not in session:
            return jsonify({'error': 'Please login first'}), 401

        data = request.get_json()
        customer_id = session['customer_id']
        beautician_id = data.get('beautician_id')
        service_ids = data.get('service_ids')
        appointment_date = data.get('date')
        appointment_time = data.get('time')

        if not all([service_ids, appointment_date, appointment_time]):
            return jsonify({'error': 'Missing required fields'}), 400

        cur = mysql.connection.cursor()
        cur.execute("""
            INSERT INTO Appointments 
            (CustomerID, BeauticianID, Appointment_Date, Appointment_Time, Status) 
            VALUES (%s, %s, %s, %s, 'scheduled')
        """, (customer_id, beautician_id, appointment_date, appointment_time))

        appointment_id = cur.lastrowid

        for service_id in service_ids:
            cur.execute("SELECT Price FROM Services WHERE ServiceID = %s", (service_id,))
            service = cur.fetchone()
            cur.execute("""
                INSERT INTO Appointment_Service (AppointmentID, ServiceID, Price_at_booking) 
                VALUES (%s, %s, %s)
            """, (appointment_id, service_id, service['Price']))

        mysql.connection.commit()
        cur.close()

        return jsonify({'message': 'Appointment created successfully', 'appointment_id': appointment_id}), 201

    except Exception as e:
        mysql.connection.rollback()
        return jsonify({'error': str(e)}), 500

@app.route('/api/appointments/my', methods=['GET'])
def get_my_appointments():
    try:
        if 'customer_id' not in session:
            return jsonify({'error': 'Please login first'}), 401

        cur = mysql.connection.cursor()
        cur.execute("""
            SELECT 
                a.AppointmentID,
                a.Appointment_Date,
                a.Appointment_Time,
                a.Status,
                CONCAT(b.First_Name, ' ', b.Last_Name) as Beautician_Name,
                GROUP_CONCAT(s.ServiceName SEPARATOR ', ') as Services,
                SUM(aps.Price_at_booking) as Total_Price
            FROM Appointments a
            LEFT JOIN Beauticians b ON a.BeauticianID = b.BeauticianID
            INNER JOIN Appointment_Service aps ON a.AppointmentID = aps.AppointmentID
            INNER JOIN Services s ON aps.ServiceID = s.ServiceID
            WHERE a.CustomerID = %s
            GROUP BY a.AppointmentID
            ORDER BY a.Appointment_Date DESC, a.Appointment_Time DESC
        """, (session['customer_id'],))

        appointments = cur.fetchall()
        cur.close()
        return jsonify(appointments), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/appointments/<int:appointment_id>/cancel', methods=['PUT'])
def cancel_appointment(appointment_id):
    try:
        if 'customer_id' not in session:
            return jsonify({'error': 'Please login first'}), 401

        cur = mysql.connection.cursor()
        cur.execute("""
            UPDATE Appointments 
            SET Status = 'cancelled' 
            WHERE AppointmentID = %s AND CustomerID = %s
        """, (appointment_id, session['customer_id']))

        mysql.connection.commit()
        cur.close()
        return jsonify({'message': 'Appointment cancelled successfully'}), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

# ============= ADMIN API ROUTES =============

@app.route('/api/admin/appointments', methods=['GET'])
def get_all_appointments():
    try:
        if 'role' not in session or session['role'] not in ['admin', 'staff']:
            return jsonify({'error': 'Unauthorized'}), 403

        cur = mysql.connection.cursor()
        cur.execute("""
            SELECT 
                a.AppointmentID,
                CONCAT(c.First_Name, ' ', c.Last_Name) as Customer_Name,
                c.PhoneNumber,
                a.Appointment_Date,
                a.Appointment_Time,
                a.Status,
                CONCAT(b.First_Name, ' ', b.Last_Name) as Beautician_Name,
                GROUP_CONCAT(s.ServiceName SEPARATOR ', ') as Services,
                SUM(aps.Price_at_booking) as Total_Price
            FROM Appointments a
            INNER JOIN Customers c ON a.CustomerID = c.CustomerID
            LEFT JOIN Beauticians b ON a.BeauticianID = b.BeauticianID
            INNER JOIN Appointment_Service aps ON a.AppointmentID = aps.AppointmentID
            INNER JOIN Services s ON aps.ServiceID = s.ServiceID
            GROUP BY a.AppointmentID
            ORDER BY a.Appointment_Date, a.Appointment_Time
        """)

        appointments = cur.fetchall()
        cur.close()
        return jsonify(appointments), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/admin/appointments/<int:appointment_id>/status', methods=['PUT'])
def update_appointment_status(appointment_id):
    try:
        if 'role' not in session or session['role'] not in ['admin', 'staff']:
            return jsonify({'error': 'Unauthorized'}), 403

        data = request.get_json()
        new_status = data.get('status')

        if new_status not in ['scheduled', 'completed', 'cancelled', 'no-show']:
            return jsonify({'error': 'Invalid status'}), 400

        cur = mysql.connection.cursor()
        cur.execute("UPDATE Appointments SET Status = %s WHERE AppointmentID = %s", (new_status, appointment_id))
        mysql.connection.commit()
        cur.close()
        return jsonify({'message': 'Status updated successfully'}), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/admin/customers/search', methods=['GET'])
def search_customers():
    try:
        if 'role' not in session or session['role'] not in ['admin', 'staff']:
            return jsonify({'error': 'Unauthorized'}), 403

        search_term = request.args.get('q', '')
        cur = mysql.connection.cursor()
        cur.execute("""
            SELECT CustomerID, First_Name, Last_Name, Email, PhoneNumber, DateRegistered
            FROM Customers
            WHERE First_Name LIKE %s OR Last_Name LIKE %s OR Email LIKE %s OR PhoneNumber LIKE %s
            LIMIT 20
        """, (f'%{search_term}%',) * 4)

        customers = cur.fetchall()
        cur.close()
        return jsonify(customers), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/admin/stats', methods=['GET'])
def get_stats():
    try:
        if 'role' not in session or session['role'] not in ['admin', 'staff']:
            return jsonify({'error': 'Unauthorized'}), 403

        cur = mysql.connection.cursor()

        cur.execute("SELECT COUNT(*) as count FROM Customers")
        total_customers = cur.fetchone()['count']

        cur.execute("""
            SELECT COUNT(*) as count FROM Appointments 
            WHERE MONTH(Appointment_Date) = MONTH(CURDATE()) 
              AND YEAR(Appointment_Date) = YEAR(CURDATE())
        """)
        appointments_this_month = cur.fetchone()['count']

        cur.execute("""
            SELECT SUM(aps.Price_at_booking) as revenue
            FROM Appointments a
            INNER JOIN Appointment_Service aps ON a.AppointmentID = aps.AppointmentID
            WHERE MONTH(a.Appointment_Date) = MONTH(CURDATE()) 
              AND YEAR(a.Appointment_Date) = YEAR(CURDATE())
              AND a.Status = 'completed'
        """)
        revenue = cur.fetchone()['revenue'] or 0

        cur.execute("""
            SELECT s.ServiceName, COUNT(*) as count
            FROM Appointment_Service aps
            INNER JOIN Services s ON aps.ServiceID = s.ServiceID
            INNER JOIN Appointments a ON aps.AppointmentID = a.AppointmentID
            WHERE a.Status = 'completed'
            GROUP BY s.ServiceID
            ORDER BY count DESC
            LIMIT 5
        """)
        popular_services = cur.fetchall()
        cur.close()

        return jsonify({
            'total_customers': total_customers,
            'appointments_this_month': appointments_this_month,
            'revenue_this_month': float(revenue),
            'popular_services': popular_services
        }), 200

    except Exception as e:
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    app.run(debug=True, port=5000)