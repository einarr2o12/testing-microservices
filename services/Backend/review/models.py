from app import db
from datetime import datetime

class Review(db.Model):
    __tablename__ = 'reviews'
    
    id = db.Column(db.Integer, primary_key=True)
    product_id = db.Column(db.Integer, nullable=False)
    rating = db.Column(db.Integer, nullable=False)
    comment = db.Column(db.Text, nullable=True)
    created_at = db.Column(db.DateTime, default=datetime.utcnow)
    
    # Renamed from 'metadata' to 'review_metadata' to avoid SQLAlchemy conflict
    review_metadata = db.Column(db.JSON, nullable=True)
    
    def __repr__(self):
        return f'<Review {self.id} for Product {self.product_id}>'