from flask import Flask, request, jsonify
from flask_sqlalchemy import SQLAlchemy
import os
import requests
import logging

# Set up logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Initialize Flask app
app = Flask(__name__)

# Database configuration
db_uri = os.environ.get('SQLALCHEMY_DATABASE_URI', 'postgresql://postgres:password@postgres/review_db')
app.config['SQLALCHEMY_DATABASE_URI'] = db_uri
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

# Initialize the database
db = SQLAlchemy(app)

# Services configuration
PRODUCT_SERVICE_URL = os.environ.get('PRODUCT_SERVICE_URL', 'http://product-service')

# Define models here to avoid circular imports
from datetime import datetime

class Review(db.Model):
    __tablename__ = 'reviews'
    
    id = db.Column(db.Integer, primary_key=True)
    product_id = db.Column(db.Integer, nullable=False)
    rating = db.Column(db.Integer, nullable=False)
    comment = db.Column(db.Text, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    review_metadata = db.Column(db.JSON, nullable=True)
    
    def __repr__(self):
        return f'<Review {self.id} for Product {self.product_id}>'

    def to_dict(self):
        return {
            'id': self.id,
            'product_id': self.product_id,
            'rating': self.rating,
            'comment': self.comment,
            'created_at': self.created_at.isoformat() if self.created_at else None,
            'review_metadata': self.review_metadata
        }

# Create database tables
try:
    with app.app_context():
        logger.info(f"Connecting to database at {db_uri}")
        db.create_all()
        logger.info("Database tables created successfully")
except Exception as e:
    logger.error(f"Error creating database tables: {str(e)}")

@app.route('/api/reviews', methods=['GET'])
def get_reviews():
    try:
        reviews = Review.query.all()
        return jsonify([review.to_dict() for review in reviews])
    except Exception as e:
        logger.error(f"Error retrieving reviews: {str(e)}")
        return jsonify({"error": str(e)}), 500

@app.route('/api/reviews/<int:review_id>', methods=['GET'])
def get_review(review_id):
    try:
        review = Review.query.get_or_404(review_id)
        return jsonify(review.to_dict())
    except Exception as e:
        logger.error(f"Error retrieving review {review_id}: {str(e)}")
        return jsonify({"error": str(e)}), 500

@app.route('/api/reviews', methods=['POST'])
def create_review():
    try:
        data = request.json
        
        if not data:
            return jsonify({"error": "No data provided"}), 400
            
        # Check for required fields
        if 'product_id' not in data:
            return jsonify({"error": "product_id is required"}), 400
        if 'rating' not in data:
            return jsonify({"error": "rating is required"}), 400
        
        # Validate product exists by calling product service
        try:
            product_response = requests.get(f"{PRODUCT_SERVICE_URL}/api/products/{data['product_id']}")
            if product_response.status_code != 200:
                return jsonify({"error": "Product not found"}), 400
        except requests.RequestException as e:
            logger.error(f"Error validating product: {str(e)}")
            return jsonify({"error": f"Error validating product: {str(e)}"}), 500
        
        # Create review
        review = Review(
            product_id=data['product_id'],
            rating=data['rating'],
            comment=data.get('comment', ''),
            review_metadata=data.get('review_metadata', {})
        )
        
        db.session.add(review)
        db.session.commit()
        
        return jsonify(review.to_dict()), 201
    except Exception as e:
        logger.error(f"Error creating review: {str(e)}")
        db.session.rollback()
        return jsonify({"error": str(e)}), 500

@app.route('/api/reviews/<int:review_id>', methods=['PUT'])
def update_review(review_id):
    try:
        review = Review.query.get_or_404(review_id)
        data = request.json
        
        if not data:
            return jsonify({"error": "No data provided"}), 400
        
        # If product_id is being changed, validate the new product exists
        if 'product_id' in data and data['product_id'] != review.product_id:
            try:
                product_response = requests.get(f"{PRODUCT_SERVICE_URL}/api/products/{data['product_id']}")
                if product_response.status_code != 200:
                    return jsonify({"error": "Product not found"}), 400
            except requests.RequestException as e:
                logger.error(f"Error validating product: {str(e)}")
                return jsonify({"error": f"Error validating product: {str(e)}"}), 500
        
        # Update review
        if 'product_id' in data:
            review.product_id = data['product_id']
        if 'rating' in data:
            review.rating = data['rating']
        if 'comment' in data:
            review.comment = data['comment']
        if 'review_metadata' in data:
            review.review_metadata = data['review_metadata']
        
        db.session.commit()
        
        return jsonify(review.to_dict())
    except Exception as e:
        logger.error(f"Error updating review {review_id}: {str(e)}")
        db.session.rollback()
        return jsonify({"error": str(e)}), 500

@app.route('/api/reviews/<int:review_id>', methods=['DELETE'])
def delete_review(review_id):
    try:
        review = Review.query.get_or_404(review_id)
        db.session.delete(review)
        db.session.commit()
        return jsonify({"message": "Review deleted successfully"})
    except Exception as e:
        logger.error(f"Error deleting review {review_id}: {str(e)}")
        db.session.rollback()
        return jsonify({"error": str(e)}), 500

@app.route('/api/products/<int:product_id>/reviews', methods=['GET'])
def get_product_reviews(product_id):
    try:
        # Validate product exists
        try:
            product_response = requests.get(f"{PRODUCT_SERVICE_URL}/api/products/{product_id}")
            if product_response.status_code != 200:
                return jsonify({"error": "Product not found"}), 404
        except requests.RequestException as e:
            logger.error(f"Error validating product: {str(e)}")
            return jsonify({"error": f"Error validating product: {str(e)}"}), 500
        
        reviews = Review.query.filter_by(product_id=product_id).all()
        return jsonify([review.to_dict() for review in reviews])
    except Exception as e:
        logger.error(f"Error retrieving reviews for product {product_id}: {str(e)}")
        return jsonify({"error": str(e)}), 500

@app.route('/health', methods=['GET'])
def health_check():
    try:
        # Simple database connectivity check
        Review.query.limit(1).all()
        return jsonify({"status": "UP", "database": "connected"})
    except Exception as e:
        logger.error(f"Health check failed: {str(e)}")
        return jsonify({"status": "DOWN", "error": str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)