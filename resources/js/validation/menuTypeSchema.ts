import { z } from 'zod';

export const menuTypeSchema = z.object({
    name: z.string().min(1, 'Name is required').max(255, 'Name must be less than 255 characters'),
    description: z.string().nullable().optional(),
    image_url: z.string().nullable().optional(),
    outlet_id: z
        .number({ required_error: 'Outlet is required' })
        .int()
        .positive('Outlet is required'),
    sort_order: z.number().int().min(0, 'Sort order must be 0 or greater').default(0),
    status: z.boolean().default(true),
});

export type MenuTypeSchemaType = z.infer<typeof menuTypeSchema>;
