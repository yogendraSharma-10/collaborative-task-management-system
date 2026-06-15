/**
 * @file client/src/api/taskService.js
 * @description Service for interacting with the Task Management API.
 * This module provides functions to perform CRUD operations and other task-related actions
 * by making HTTP requests to the backend API.
 */

import axios from 'axios';

// Base URL for the API, fetched from environment variables.
// This allows for easy switching between development, staging, and production environments.
const API_BASE_URL = process.env.REACT_APP_API_BASE_URL || 'http://localhost:8000/api';

// Create an Axios instance with a base URL and default headers.
// This centralizes configuration and allows for interceptors (e.g., for authentication).
const taskApiClient = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    // In a real application, you would typically include an Authorization header here
    // with a JWT or other token obtained after user login.
    // 'Authorization': `Bearer ${localStorage.getItem('authToken')}`
  },
});

/**
 * Helper function to get the authentication token.
 * In a production app, this would likely come from a global state (e.g., Redux, Context API)
 * or a secure storage mechanism.
 * @returns {string | null} The authentication token or null if not found.
 */
const getAuthToken = () => {
  // Placeholder: Retrieve token from localStorage or a more secure client-side storage
  // For a real application, consider HttpOnly cookies or a robust state management solution.
  return localStorage.getItem('authToken');
};

// Add a request interceptor to include the auth token in every request.
taskApiClient.interceptors.request.use(
  (config) => {
    const token = getAuthToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

/**
 * Fetches all tasks for a specific project.
 * @param {string} projectId The ID of the project.
 * @returns {Promise<Array>} A promise that resolves to an array of task objects.
 * @throws {Error} If the API call fails.
 */
export const getAllTasks = async (projectId) => {
  try {
    const response = await taskApiClient.get(`/projects/${projectId}/tasks`);
    return response.data;
  } catch (error) {
    console.error(`Error fetching tasks for project ${projectId}:`, error);
    throw error; // Re-throw to allow components to handle the error
  }
};

/**
 * Fetches a single task by its ID.
 * @param {string} taskId The ID of the task.
 * @returns {Promise<Object>} A promise that resolves to a task object.
 * @throws {Error} If the API call fails or the task is not found.
 */
export const getTaskById = async (taskId) => {
  try {
    const response = await taskApiClient.get(`/tasks/${taskId}`);
    return response.data;
  } catch (error) {
    console.error(`Error fetching task ${taskId}:`, error);
    throw error;
  }
};

/**
 * Creates a new task.
 * @param {Object} taskData The data for the new task (e.g., title, description, projectId, assignedTo, dueDate).
 * @returns {Promise<Object>} A promise that resolves to the newly created task object.
 * @throws {Error} If the API call fails.
 */
export const createTask = async (taskData) => {
  try {
    const response = await taskApiClient.post('/tasks', taskData);
    return response.data;
  } catch (error) {
    console.error('Error creating task:', error);
    throw error;
  }
};

/**
 * Updates an existing task.
 * @param {string} taskId The ID of the task to update.
 * @param {Object} taskData The updated data for the task.
 * @returns {Promise<Object>} A promise that resolves to the updated task object.
 * @throws {Error} If the API call fails.
 */
export const updateTask = async (taskId, taskData) => {
  try {
    const response = await taskApiClient.put(`/tasks/${taskId}`, taskData);
    return response.data;
  } catch (error) {
    console.error(`Error updating task ${taskId}:`, error);
    throw error;
  }
};

/**
 * Deletes a task.
 * @param {string} taskId The ID of the task to delete.
 * @returns {Promise<Object>} A promise that resolves to a confirmation object/message.
 * @throws {Error} If the API call fails.
 */
export const deleteTask = async (taskId) => {
  try {
    const response = await taskApiClient.delete(`/tasks/${taskId}`);
    return response.data;
  } catch (error) {
    console.error(`Error deleting task ${taskId}:`, error);
    throw error;
  }
};

/**
 * Updates the status of a specific task.
 * This uses a PATCH request for partial updates, which is more efficient.
 * @param {string} taskId The ID of the task to update.
 * @param {string} status The new status (e.g., 'pending', 'in-progress', 'completed').
 * @returns {Promise<Object>} A promise that resolves to the updated task object.
 * @throws {Error} If the API call fails.
 */
export const updateTaskStatus = async (taskId, status) => {
  try {
    const response = await taskApiClient.patch(`/tasks/${taskId}/status`, { status });
    return response.data;
  } catch (error) {
    console.error(`Error updating status for task ${taskId}:`, error);
    throw error;
  }
};

/**
 * Assigns a task to a user.
 * This might involve interacting with a 'UserService' or 'AuthService' in a microservice architecture
 * to validate user IDs, but for this service, it just sends the user ID to the task API.
 * @param {string} taskId The ID of the task to assign.
 * @param {string} userId The ID of the user to assign the task to.
 * @returns {Promise<Object>} A promise that resolves to the updated task object.
 * @throws {Error} If the API call fails.
 */
export const assignTask = async (taskId, userId) => {
  try {
    const response = await taskApiClient.patch(`/tasks/${taskId}/assign`, { assigned_to_user_id: userId });
    return response.data;
  } catch (error) {
    console.error(`Error assigning task ${taskId} to user ${userId}:`, error);
    throw error;
  }
};

/**
 * Unassigns a task from a user.
 * @param {string} taskId The ID of the task to unassign.
 * @returns {Promise<Object>} A promise that resolves to the updated task object.
 * @throws {Error} If the API call fails.
 */
export const unassignTask = async (taskId) => {
  try {
    // Sending null or an empty string for assigned_to_user_id to indicate unassignment
    const response = await taskApiClient.patch(`/tasks/${taskId}/assign`, { assigned_to_user_id: null });
    return response.data;
  } catch (error) {
    console.error(`Error unassigning task ${taskId}:`, error);
    throw error;
  }
};

// Export all functions as a single object for convenience
const taskService = {
  getAllTasks,
  getTaskById,
  createTask,
  updateTask,
  deleteTask,
  updateTaskStatus,
  assignTask,
  unassignTask,
};

export default taskService;