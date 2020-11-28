#!/usr/bin/env python
# coding: utf-8

# In[1]:


import boto3
import io
import numpy as np
import pandas as pd
import sagemaker
import seaborn

from sagemaker import KMeans
from sklearn.preprocessing import MinMaxScaler


# In[2]:


s3_client = boto3.client('s3')
s3_bucket = "wheeler-cloud-guru-challenge-1020"
s3_object = "athena-results/imdb.csv"

imdb_data = s3_client.get_object(Bucket=s3_bucket, Key=s3_object)
imdb_body = imdb_data["Body"].read()
imdb_df = pd.read_csv(io.BytesIO(imdb_body), header=0, delimiter=",", low_memory=False)
imdb_df


# In[3]:


year_df = imdb_df.groupby('year')['id'].nunique()
year_df 


# In[4]:


seaborn.set(style='darkgrid')
seaborn.lineplot(data=year_df)


# In[5]:


imdb_df['rating'] = imdb_df['rating'].apply(lambda x: int(round(x)))
rating_df = imdb_df.groupby('rating')['id'].nunique()
rating_df


# In[6]:


seaborn.set(style='whitegrid')
seaborn.lineplot(data=rating_df)


# In[8]:


imdb_df['idx'] = range(1, len(imdb_df) + 1)
result_df = imdb_df[['id', 'idx', 'genres', 'rating', 'title', 'votes', 'year']]
result_df


# In[9]:


del imdb_df['id']
del imdb_df['genres']
del imdb_df['title']

imdb_df = imdb_df.astype('float32')
imdb_df


# In[11]:


scaler = MinMaxScaler()
scaler_columns = ['year', 'votes']
imdb_df[scaler_columns] = pd.DataFrame(scaler.fit_transform(imdb_df[scaler_columns]))
imdb_df


# In[12]:


sage_session = sagemaker.Session()
sage_outputs = f"s3://{sage_session.default_bucket()}/imdb/"

kmeans = KMeans(role=sagemaker.get_execution_role(),
                instance_count=1,
                instance_type='ml.c4.xlarge',
                output_path=sage_outputs,              
                k=9)


# In[13]:


get_ipython().run_cell_magic('time', '', 'train_data = imdb_df.values\nkmeans.fit(kmeans.record_set(train_data))')


# In[14]:


get_ipython().run_cell_magic('time', '', "model = kmeans.deploy(initial_instance_count=1,instance_type='ml.m4.xlarge')")


# In[17]:


predictions = model.predict(train_data)
print(f"predictions[0]:\n{predictions[0]}")


# In[18]:


sagemaker.Session().delete_endpoint(model.endpoint_name)


# In[19]:


result_df['cluster'] = np.nan
result_df['distance'] = np.nan

for i, p in enumerate(predictions):
    result_df.at[i, 'cluster'] = p.label['closest_cluster'].float32_tensor.values[0]
    result_df.at[i, 'distance'] = p.label['distance_to_cluster'].float32_tensor.values[0]
    
result_df


# In[20]:


result_df.to_csv('imdb_predictions.csv', encoding='utf-8', index=False)


# In[21]:


get_ipython().system(' aws s3 cp "imdb_predictions.csv" "s3://wheeler-cloud-guru-challenge-1020/sagemaker-results/"')

