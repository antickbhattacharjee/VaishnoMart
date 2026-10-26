"""
VaishnoMart Machine Learning Recommendation Engine
Uses TF-IDF Vectorization, Cosine Similarity, and Co-occurrence Matrix Analysis
(scikit-learn, numpy, pandas) to produce high-accuracy product recommendations.
"""

import sys
import json
import os
import argparse
from typing import Dict, List, Any

try:
    import numpy as np
    import pandas as pd
    from sklearn.feature_extraction.text import TfidfVectorizer
    from sklearn.metrics.pairwise import cosine_similarity
except ImportError as e:
    sys.stderr.write(f"Error importing ML libraries: {e}\n")
    sys.exit(1)


def build_recommendation_model(products: List[Dict[str, Any]], order_baskets: List[List[int]]) -> Dict[str, Any]:
    """
    Build content-based and collaborative recommendation matrices.
    """
    if not products:
        return {
            "metadata": {"total_products": 0, "status": "empty"},
            "content_similarity": {},
            "frequently_bought_together": {}
        }

    df = pd.DataFrame(products)
    
    # Feature Engineering: Combine Title, Category, Description, and Unit into rich feature text
    def create_feature_corpus(row):
        category = str(row.get('category_name', ''))
        name = str(row.get('name', ''))
        description = str(row.get('description', ''))
        unit = str(row.get('unit', ''))
        specs = str(row.get('specifications', ''))
        
        # Give higher weight to Category and Product Name by repeating them
        return f"{category} {category} {name} {name} {name} {description} {unit} {specs}".lower()

    df['corpus'] = df.apply(create_feature_corpus, axis=1)

    # Content-Based Filtering: TF-IDF Vectorization & Cosine Similarity
    vectorizer = TfidfVectorizer(
        stop_words='english',
        ngram_range=(1, 2),
        sublinear_tf=True,
        min_df=1
    )
    
    tfidf_matrix = vectorizer.fit_transform(df['corpus'])
    similarity_matrix = cosine_similarity(tfidf_matrix, tfidf_matrix)

    product_ids = df['id'].tolist()
    id_to_idx = {pid: idx for idx, pid in enumerate(product_ids)}
    
    content_similarities = {}
    for idx, pid in enumerate(product_ids):
        # Sort by similarity score descending, exclude self
        sim_scores = list(enumerate(similarity_matrix[idx]))
        sim_scores = sorted(sim_scores, key=lambda x: x[1], reverse=True)
        
        recommendations = []
        for other_idx, score in sim_scores:
            other_pid = product_ids[other_idx]
            if other_pid == pid:
                continue
            if score > 0.05:  # threshold filter
                recommendations.append({
                    "product_id": int(other_pid),
                    "score": round(float(score), 4)
                })
            if len(recommendations) >= 8:
                break
                
        content_similarities[str(pid)] = recommendations

    # Collaborative Filtering: Item-Item Co-occurrence from Order Baskets
    co_occurrence: Dict[int, Dict[int, int]] = {pid: {} for pid in product_ids}
    for basket in order_baskets:
        unique_items = list(set(basket))
        for i in range(len(unique_items)):
            item_a = unique_items[i]
            if item_a not in co_occurrence:
                continue
            for j in range(len(unique_items)):
                if i == j:
                    continue
                item_b = unique_items[j]
                co_occurrence[item_a][item_b] = co_occurrence[item_a].get(item_b, 0) + 1

    frequently_bought = {}
    for pid, neighbors in co_occurrence.items():
        sorted_neighbors = sorted(neighbors.items(), key=lambda x: x[1], reverse=True)
        frequently_bought[str(pid)] = [
            {"product_id": int(neighbor_id), "frequency": int(freq)}
            for neighbor_id, freq in sorted_neighbors[:5]
        ]

    # Category Clusters for fast fallback & diversity
    category_clusters = {}
    for pid, row in df.iterrows():
        cat_id = str(row.get('category_id', '0'))
        if cat_id not in category_clusters:
            category_clusters[cat_id] = []
        category_clusters[cat_id].append(int(row['id']))

    return {
        "metadata": {
            "total_products": len(products),
            "total_baskets": len(order_baskets),
            "algorithm": "Hybrid TF-IDF Cosine Similarity + Basket Co-occurrence Matrix",
            "version": "1.0.0"
        },
        "content_similarity": content_similarities,
        "frequently_bought_together": frequently_bought,
        "category_clusters": category_clusters
    }


def main():
    parser = argparse.ArgumentParser(description="Train ML Recommendation Model for VaishnoMart")
    parser.add_argument("--input", type=str, required=True, help="Path to input JSON data with products and orders")
    parser.add_argument("--output", type=str, required=True, help="Path to write the trained recommendation model JSON")
    args = parser.parse_args()

    if not os.path.exists(args.input):
        sys.stderr.write(f"Input file not found: {args.input}\n")
        sys.exit(1)

    with open(args.input, 'r', encoding='utf-8') as f:
        data = json.load(f)

    products = data.get("products", [])
    order_baskets = data.get("order_baskets", [])

    model_output = build_recommendation_model(products, order_baskets)

    os.makedirs(os.path.dirname(args.output), exist_ok=True)
    with open(args.output, 'w', encoding='utf-8') as f:
        json.dump(model_output, f, indent=2)

    print(json.dumps({
        "status": "success",
        "total_products": len(products),
        "total_baskets": len(order_baskets),
        "output": args.output
    }))


if __name__ == "__main__":
    main()
