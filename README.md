# AWS ML Recommendation Engine

A Netflix-style movie recommendation engine built with AWS SageMaker and served through a serverless PHP website using AWS Lambda and Bref.

## 🏆 Cloud Guru Challenge Submission

This project was built for the October 2020 [#CloudGuruChallenge](https://acloudguru.com/blog/engineering/cloudguruchallenge-machine-learning-on-aws) on Machine Learning with AWS.

**Live Website**: [wheelerrecommends.com](http://wheelerrecommends.com/)

**Blog Posts**: 
- [AWS ML Recommendation Engine](https://wheeleruniverse.netlify.app/aws-ml-recommendation-engine)
- [Creating Serverless Websites with AWS, Bref, and PHP](https://wheeleruniverse.netlify.app/serverless-aws-bref-php)

## 📋 Overview

This project implements a complete end-to-end machine learning solution for movie recommendations:

1. **Data Engineering**: Processing IMDB datasets using AWS Athena for feature engineering
2. **Machine Learning**: Training a K-Means clustering model with AWS SageMaker
3. **Serverless Website**: PHP-based recommendation interface using AWS Lambda and Bref
4. **Infrastructure**: Fully serverless architecture with CloudFront, API Gateway, and S3

### Key Features
- **K-Means Clustering**: Groups movies into 9 clusters based on features
- **Feature Engineering**: Custom transformations for genres, ratings, titles, and years
- **Serverless PHP**: First-class PHP support on Lambda using Bref framework
- **Global CDN**: CloudFront distribution for low-latency access
- **Cost-Effective**: Entire solution runs for pennies using serverless services

## 🏗️ Architecture

```
┌─────────────┐     ┌──────────────┐     ┌─────────────┐
│   Route53   │────▶│  CloudFront  │────▶│ API Gateway │
│    (DNS)    │     │    (CDN)     │     │   (HTTP)    │
└─────────────┘     └──────┬───────┘     └──────┬──────┘
                           │                    │
                           ▼                    ▼
                    ┌─────────────┐      ┌─────────────┐
                    │     S3      │      │   Lambda    │
                    │  (Static)   │      │    (PHP)    │
                    └─────────────┘      └──────┬──────┘
                                                │
                                                ▼
                                         ┌─────────────┐
                                         │     ECR     │
                                         │  (Images)   │
                                         └─────────────┘

Machine Learning Pipeline:
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│     S3      │────▶│   Athena    │────▶│  SageMaker  │
│ (IMDB Data) │     │    (SQL)    │     │  (K-Means)  │
└─────────────┘     └─────────────┘     └─────────────┘
```

## 🛠️ Technologies Used

### Machine Learning Stack
- **AWS SageMaker**: Notebook instances and K-Means algorithm
- **Amazon Athena**: SQL-based feature engineering
- **Amazon S3**: Data storage for IMDB datasets
- **Python Libraries**: Pandas, NumPy, Seaborn, Boto3
- **Jupyter Notebooks**: Interactive development environment

### Web Application Stack
- **AWS Lambda**: Serverless PHP execution
- **Bref Framework**: PHP runtime for Lambda
- **Amazon ECR**: Container image registry
- **API Gateway**: HTTP endpoint for Lambda
- **CloudFront**: Global content delivery
- **Route53**: DNS management
- **Docker**: Container packaging

## 🚀 Getting Started

### Prerequisites

- AWS Account with appropriate permissions
- Docker installed locally
- AWS CLI configured
- Python 3.8+ with Jupyter
- PHP 7.4+ (for local development)

### Installation

1. Clone the repository:
```bash
git clone https://github.com/wheeleruniverse/cgc-aws-ml-recommendation-engine.git
cd cgc-aws-ml-recommendation-engine
```

2. Set up environment variables:
```bash
export AWS_ACCOUNT_ID=your-account-id
export AWS_REGION=us-east-1
export AWS_ECR_REPOSITORY=recommendation-engine
```

3. Create ECR repository:
```bash
aws ecr create-repository --repository-name $AWS_ECR_REPOSITORY
```

## 🧮 Machine Learning Pipeline

### Data Sources
- **IMDB Datasets**: https://developer.imdb.com/non-commercial-datasets/
  - title.basics.tsv.gz - Movie metadata
  - title.ratings.tsv.gz - User ratings
  - title.akas.tsv.gz - Alternative titles

### Feature Engineering Process

1. **Data Upload to S3**:
```python
def s3_upload(filename):
    bucket_name = "wheeler-cloud-guru-challenges"
    object_name = f"1020/IMDB/{filename}"
    !aws s3 cp "{filename}" "s3://{bucket_name}/{object_name}"
```

2. **SQL-based Feature Engineering with Athena**:
```sql
select
    res.*,
    contains(res.genres, 'Action') isaction,
    contains(res.genres, 'Adventure') isadventure,
    contains(res.genres, 'Comedy') iscomedy,
    -- Additional genre flags...
from (
    select distinct
        tbasics.tconst id,
        tbasics.primarytitle title,
        tbasics.startyear year,
        split(tbasics.genres, ',') genres,
        tratings.averagerating rating,
        tratings.numvotes votes
    from title_basics tbasics
    join title_akas takas on tbasics.tconst = takas.titleid
    join title_ratings tratings on tbasics.tconst = tratings.tconst
    where tbasics.startyear <= year(now())
    and tbasics.titleType = 'movie'
    and tbasics.isadult = 0
    and takas.language = 'en'
    and takas.region = 'US'
) res;
```

3. **Custom Feature Transformations**:

- **Genre Mapping**: Converts genre strings to numeric values
- **Rating Adjustment**: Weights ratings based on vote count
- **Title Encoding**: Uses Unicode values for series grouping
- **Min-Max Scaling**: Normalizes all features to 0-1 range

### K-Means Model Training

```python
# Initialize K-Means with 9 clusters
kmeans = KMeans(
    role=sagemaker.get_execution_role(),
    instance_count=1,
    instance_type='ml.c4.xlarge',
    output_path=sage_outputs,
    k=9  # 9 clusters (author's favorite number)
)

# Train and deploy
kmeans.fit(kmeans.record_set(train_data))
model = kmeans.deploy(
    initial_instance_count=1,
    instance_type='ml.m4.xlarge'
)
```

## 🌐 Serverless PHP Website

### Building the Container Image

1. **Dockerfile for Bref PHP**:
```dockerfile
FROM bref/php-74-fpm
RUN curl -s https://getcomposer.org/installer | php
RUN php composer.phar require bref/bref
COPY . /var/task
CMD _HANDLER=index.php /opt/bootstrap
```

2. **Deploy to ECR**:
```bash
# Authenticate to ECR
aws ecr get-login-password --region $AWS_REGION | \
  docker login --username AWS --password-stdin \
  "$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com"

# Build and push
docker build -t $AWS_ECR_REPOSITORY .
docker tag "$AWS_ECR_REPOSITORY:latest" \
  "$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$AWS_ECR_REPOSITORY:latest"
docker push \
  "$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$AWS_ECR_REPOSITORY:latest"
```

3. **Update Lambda Function**:
```bash
aws lambda update-function-code \
  --function-name recommendation-engine \
  --image-uri "$AWS_ACCOUNT_ID.dkr.ecr.$AWS_REGION.amazonaws.com/$AWS_ECR_REPOSITORY:latest"
```

### Website Features
- Movie search functionality
- Cluster-based recommendations
- Responsive design
- Static assets served from S3
- Dynamic content from Lambda

## 📊 Data Insights

### Cluster Characteristics
The K-Means model identified 9 distinct movie clusters based on:
- Genre combinations
- Rating distributions
- Release year patterns
- Popularity (vote counts)

### Performance Metrics
- **Model Training Time**: ~5 minutes on ml.c4.xlarge
- **Prediction Latency**: <100ms per batch
- **Website Load Time**: ~2 seconds (as per Lighthouse)
- **Monthly Cost**: <$5 for entire solution

## 🧪 Testing

### Machine Learning Tests
- Validate feature engineering transformations
- Test model predictions on known movie sets
- Verify cluster stability

### Website Tests
- Lambda function unit tests
- API Gateway integration tests
- CloudFront cache behavior validation

## 💡 Lessons Learned

### Technical Insights

1. **Feature Engineering is Critical**: 
   - Initial attempts without proper feature scaling performed poorly
   - Rating adjustments based on vote counts significantly improved clustering

2. **Memory Constraints**:
   - Large IMDB datasets didn't fit in SageMaker notebook memory
   - Athena provided an elegant SQL-based solution

3. **Serverless PHP is Viable**:
   - Bref makes PHP on Lambda surprisingly straightforward
   - Container image support simplifies deployment

### Architectural Decisions

1. **Why K-Means?**
   - Simple to understand and implement
   - Fast training and inference
   - Good enough for recommendation use case

2. **Why 9 Clusters?**
   - Author's favorite number
   - Reasonable granularity for movie categories
   - Balanced between too few and too many groups

3. **Why Serverless?**
   - Minimal ongoing costs
   - Automatic scaling
   - No server management

## 💰 Cost Optimization

- **SageMaker**: Delete endpoints immediately after predictions
- **Lambda**: Use appropriate memory allocation (512MB sufficient)
- **CloudFront**: Configure cache headers for static assets
- **S3**: Use lifecycle policies for old data
- **Athena**: Only $0.02 for entire feature engineering!

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 🙏 Acknowledgments

- [A Cloud Guru](https://acloudguru.com/) for the challenge framework
- [Bref](https://bref.sh/) team for making PHP on Lambda possible
- [IMDB](https://developer.imdb.com/) for providing open datasets
- AWS community for guidance and support
- Kesha Williams for suggesting the IMDB dataset approach

## 📬 Contact

- **Website**: [wheelerrecommends.com](http://wheelerrecommends.com/)
- **GitHub**: [@wheeleruniverse](https://github.com/wheeleruniverse)
- **LinkedIn**: [linkedin.com/in/wheeleruniverse](https://linkedin.com/in/wheeleruniverse)
- **Blog**: [dev.to/wheeleruniverse](https://dev.to/wheeleruniverse)

---

*Built with ❤️ and a love for the number 9 as part of the #CloudGuruChallenge*
