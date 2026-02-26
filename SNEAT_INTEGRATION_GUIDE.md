# Sneat Template Integration Guide

## Overview
Your Pharmax project has been successfully integrated with the Sneat Bootstrap 5 Admin Template. All your article and commentaire templates now use the modern, professional Sneat design.

## Changes Made

### 1. Updated Base Layout (`templates/base.html.twig`)
- Replaced basic HTML structure with Sneat's full admin layout
- Added responsive sidebar navigation with menu items for Articles and Commentaires
- Integrated Sneat's navbar with search functionality and user dropdown
- Included all necessary Sneat CSS and JavaScript libraries
- Structured content area with proper Bootstrap 5 grid system

### 2. Article Templates
- **index.html.twig**: Modern table layout with hover effects, dropdown actions, and responsive design
- **show.html.twig**: Enhanced card-based detail view with image preview and date formatting
- **new.html.twig**: Clean form layout with validation styling
- **edit.html.twig**: Same form styling as new with edit-specific header
- **_form.html.twig**: Styled form inputs using Bootstrap 5 classes
- **_delete_form.html.twig**: Prominent danger zone with confirmation modal

### 3. Commentaire Templates
- **index.html.twig**: Table view with status badges (Approved/Pending)
- **show.html.twig**: Detailed card layout with status indicators
- **new.html.twig**: Clean comment creation form
- **edit.html.twig**: Comment editing interface
- **_form.html.twig**: Styled form with content and status fields
- **_delete_form.html.twig**: Delete confirmation with warning message

## Asset Configuration

### Important: Configure Asset Paths

The Sneat template assets need to be accessible via the `asset()` function. You have two options:

#### Option 1: Create a Symlink (Recommended for Development)
```bash
# Run from your project root
mkdir -p public/sneat
cd public/sneat
mklink /D assets ..\..\templates\sneat-1.0.0\assets
```

#### Option 2: Copy Assets to Public Directory
```bash
# Copy the sneat assets folder to public
xcopy templates\sneat-1.0.0\assets public\sneat\assets /E /I
```

#### Option 3: Configure Asset Mapper
Edit `config/packages/asset_mapper.yaml` to include:
```yaml
framework:
    asset_mapper:
        paths:
            sneat: templates/sneat-1.0.0
```

## Navigation Structure

The sidebar menu now includes:
- **Articles**: Links to article management
- **Commentaires**: Links to comment management

Active menu items are automatically highlighted based on the current route.

## Features Implemented

### 1. Responsive Design
- Works on desktop, tablet, and mobile devices
- Collapsible sidebar on smaller screens
- Bootstrap 5 grid system throughout

### 2. Professional Styling
- Consistent color scheme with primary blue (#696cff)
- Card-based layouts for content organization
- Proper spacing and typography
- Rounded corners and subtle shadows

### 3. Enhanced User Experience
- Dropdown menus for actions (View, Edit, Delete)
- Status badges for comment states
- Confirmation dialogs for destructive actions
- Loading states and visual feedback

### 4. Data Display
- Image thumbnails in article lists
- Truncated content preview in comment lists
- Formatted dates using Twig date filter
- Empty state messages with icons

## Bootstrap 5 Classes Used

Common classes throughout the templates:
- `btn`, `btn-primary`, `btn-success`, `btn-danger`, `btn-outline-*`: Button styles
- `table`, `table-hover`, `table-light`: Table styling
- `card`, `card-body`, `card-header`, `card-title`: Card components
- `badge`, `badge-label-*`: Status badges
- `form-control`, `form-label`, `form-select`: Form elements
- `alert`, `alert-danger`, `alert-light`: Alert components
- `dropdown`, `dropdown-menu`, `dropdown-item`: Dropdown menus
- `d-flex`, `justify-content-between`, `align-items-center`: Flexbox utilities
- `mb-3`, `mt-4`, `p-4`: Spacing utilities

## Next Steps

1. **Configure Assets** (Required):
   - Choose one of the asset configuration options above
   - Verify assets load by checking browser console

2. **Customize Colors** (Optional):
   - Edit `templates/sneat-1.0.0/assets/css/demo.css`
   - Or override colors in your custom CSS

3. **Add Authentication** (Recommended):
   - Update the user dropdown in `base.html.twig`
   - Add logout functionality
   - Display actual user information

4. **Extend Navigation** (As needed):
   - Add more menu items to the sidebar
   - Create additional admin pages
   - Follow the same template structure

## Testing

After configuring assets, verify:
1. Sidebar displays correctly
2. Navbar appears at the top
3. Menu items navigate to correct pages
4. Tables and forms display properly
5. Images load if present
6. Responsive design works on mobile

## Troubleshooting

### Assets Not Loading
- Check browser console for 404 errors
- Verify asset path in `base.html.twig`
- Ensure symlink or copy was successful

### Sidebar Not Collapsing on Mobile
- JavaScript may not be loading correctly
- Clear browser cache and reload
- Check that all script tags in `base.html.twig` are loading

### Form Validation Not Styling
- Ensure form includes `novalidate` attribute
- Check that Bootstrap CSS is loaded first
- Verify form helper is outputting proper classes

## File Locations

```
pharmax/
├── templates/
│   ├── base.html.twig (Updated)
│   ├── article/
│   │   ├── index.html.twig (Updated)
│   │   ├── show.html.twig (Updated)
│   │   ├── new.html.twig (Updated)
│   │   ├── edit.html.twig (Updated)
│   │   ├── _form.html.twig (Updated)
│   │   └── _delete_form.html.twig (Updated)
│   ├── commentaire/
│   │   ├── index.html.twig (Updated)
│   │   ├── show.html.twig (Updated)
│   │   ├── new.html.twig (Updated)
│   │   ├── edit.html.twig (Updated)
│   │   ├── _form.html.twig (Updated)
│   │   └── _delete_form.html.twig (Updated)
│   └── sneat-1.0.0/
│       ├── assets/ (CSS, JS, images)
│       ├── html/ (Reference templates)
│       └── ...
└── public/
    └── sneat/ (Link or copy sneat/assets here)
```

## Support

For Sneat template documentation, visit:
https://themeselection.com/demo/sneat-bootstrap-html-admin-template/documentation/

For Bootstrap 5 documentation, visit:
https://getbootstrap.com/docs/5.0/
