import fs from 'fs';
import path from 'path';

// Written by auth.setup.ts, read by the specs.
export const authDir = path.join(__dirname, '..', '.auth');
export const usersFile = path.join(authDir, 'users.json');
export const storageFor = (role: Role) => path.join(authDir, `${role}.json`);

export type Role = 'author' | 'voter';
export type User = { username: string; email: string; password: string };

export function loadUsers(): Record<Role, User> {
  return JSON.parse(fs.readFileSync(usersFile, 'utf8'));
}
