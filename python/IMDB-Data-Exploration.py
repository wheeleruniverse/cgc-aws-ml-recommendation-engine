#!/usr/bin/env python
# coding: utf-8

# In[1]:


import pandas as pd


# In[2]:


get_ipython().system('ls -hl *.tsv')


# In[3]:


def sample_df(name):
    """
    creates a dataframe for the first 500 lines from a tab-seperated file
    returns the dataframe
    """
    print(f"Sampling: {name}")
    df = pd.read_csv(name, nrows=500, sep='\t')
    return df


# In[4]:


name_basics = "name_basics.tsv"
name_basics_df = sample_df(name_basics)
name_basics_df


# In[6]:


title_akas = "title_akas.tsv"
title_akas_df = sample_df(title_akas)
title_akas_df


# In[7]:


title_basics = "title_basics.tsv"
title_basics_df = sample_df(title_basics)
title_basics_df


# In[8]:


title_crew = "title_crew.tsv"
title_crew_df = sample_df(title_crew)
title_crew_df


# In[9]:


title_episode = "title_episode.tsv"
title_episode_df = sample_df(title_episode)
title_episode_df


# In[10]:


title_principals = "title_principals.tsv"
title_principals_df = sample_df(title_principals)
title_principals_df


# In[11]:


title_ratings = "title_ratings.tsv"
title_ratings_df = sample_df(title_ratings)
title_ratings_df


# In[ ]:




